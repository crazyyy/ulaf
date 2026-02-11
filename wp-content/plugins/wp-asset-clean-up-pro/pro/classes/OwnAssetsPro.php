<?php
namespace WpAssetCleanUpPro;

use WpAssetCleanUp\OwnAssets;

/**
 *
 */
class OwnAssetsPro
{
	/**
	 * @var bool
	 */
	public $isTaxonomyEditPage = false;

	/**
	 * @return bool
	 */
	public function isTaxonomyEditPage()
	{
		if ( ! $this->isTaxonomyEditPage ) {
			$wpacuMainPro = new MainPro();
			$this->isTaxonomyEditPage = $wpacuMainPro->isTaxonomyEditPage();
		}

		return $this->isTaxonomyEditPage;
	}

	/**
	 * This code is called from class: "OwnAssets" - method: "enqueueAdminScripts"
	 *
	 * @return void
	 */
	public static function originEnqueueAdminScripts()
	{
		/*
		 * [START] Critical CSS
		 */
		if (isset($_GET['page'], $_GET['wpacu_sub_page']) &&
		    $_GET['page'] === WPACU_PLUGIN_ID . '_assets_manager' &&
		    $_GET['wpacu_sub_page'] === 'manage_critical_css') {
			wp_enqueue_script( 'wp-theme-plugin-editor' );
			wp_enqueue_style( 'wp-codemirror' );

			$cm_settings = array();
			$cm_settings['codeEditor'] = wp_enqueue_code_editor( array( 'type' => 'text/css' ) );
			$customPagesInlineJS = ''; // only fills if the "Custom Pages" tab is used

			if (isset($_GET['wpacu_for']) && $_GET['wpacu_for'] === 'custom-pages') {
				$cm_settings_custom_pages = array();
				$cm_settings_custom_pages['codeEditor'] = wp_enqueue_code_editor( array( 'type' => 'text/x-php' ) );
				wp_localize_script( 'jquery', 'wpacu_cm_settings_custom_pages', $cm_settings_custom_pages );

				$customPagesInlineJS = <<<JS
// Custom Pages
wp.codeEditor.initialize($('#wpacu-php-editor-textarea'), wpacu_cm_settings_custom_pages);
JS;
			}

			wp_localize_script( 'jquery', 'wpacu_cm_settings', $cm_settings );

			$wpacuCodeMirrorInlineScript = <<<JS
jQuery(document).ready(function($) {
  // Editable CSS
  var wpacuEditor = wp.codeEditor.initialize($('#wpacu-css-editor-textarea'), wpacu_cm_settings);
  
  {$customPagesInlineJS}
  
  $(document).on('change', '#wpacu_critical_css_status', function() {
      var \$wpacuTargetElement     = $('#wpacu-critical-css-options-area'),
          \$wpacuTargetTabMenuItem = $('#wpacu-critical-css-manager-tab-menu').find('.nav-tab-active > .wpacu-circle-status'),
          wpacuAnyCustomPageType   = $(this).attr('data-wpacu-custom-page-type');
      
      if ($(this).prop('checked')) {
          \$wpacuTargetElement.removeClass('wpacu-faded');
          
          if (wpacuAnyCustomPageType !== '') {
              $('.wpacu-circle-status[data-wpacu-custom-page-type="'+ wpacuAnyCustomPageType +'"]')
                .removeClass('wpacu-off').addClass('wpacu-on');
          }
          
          \$wpacuTargetTabMenuItem.removeClass('wpacu-off').addClass('wpacu-on');
      } else {
          \$wpacuTargetElement.addClass('wpacu-faded');
          
          if (wpacuAnyCustomPageType !== '') {
              $('.wpacu-circle-status[data-wpacu-custom-page-type="'+ wpacuAnyCustomPageType +'"]')
                .removeClass('wpacu-on').addClass('wpacu-off');
          }
          
          /* In case there are any other custom post types with critical CSS enabled, then keep the green circle for the "Custom Posty Types" main tab */
          if ($('#wpacu_custom_pages_nav_links').find('.wpacu-circle-status.wpacu-on').length === 0) {
              \$wpacuTargetTabMenuItem.removeClass('wpacu-on').addClass('wpacu-off');
          }
      }
  });
  
  $(document).on('submit', '#wpacu-critical-css-form', function() {
      if (wpacuEditor.codemirror.getValue() === '' && $('#wpacu_critical_css_status').prop('checked')) {
          alert('You have chosen to activate the critical CSS. You need to provide the CSS content before submitting this form.');
          return false;
      }
      
      $('#wpacu-updating-critical-css').addClass('wpacu-show').removeClass('wpacu-hide');
      $('#wpacu-update-critical-css-button-area').find('.button').prop('disabled', true).attr('value', 'UPDATING...');
  });
})
JS;
			$wpacuCodeMirrorInlineStyle = <<<CSS
.CodeMirror {
  border: 1px solid #ddd;
}

/* "CSS & JS Manager" -- "Manage Critical CSS" -- "Custom Pages" */
#wpacu-critical-css-custom-pages .CodeMirror {
    height: auto;
}

#wpacu-critical-css-options-area.wpacu-faded {
    opacity: 0.4;
}

#wpacu-css-editor-textarea {
    width: 100%;
    min-height: 600px;
}

#wpacu-update-critical-css-button-area {
    display: inline-block;
    margin: 20px 0 0 0;
}

#wpacu-update-critical-css-button-area input {
    padding: 5px 18px;
    height: 45px;
    font-size: 15px;
}

#wpacu-updating-critical-css.wpacu-hide {
    display: none;
}

#wpacu-updating-critical-css.wpacu-show {
    display: inline-block;
    margin: 13px 0 0 8px;
}
CSS;
			wp_add_inline_script('wp-theme-plugin-editor', $wpacuCodeMirrorInlineScript);
			wp_add_inline_style('wp-codemirror', $wpacuCodeMirrorInlineStyle);
		}
		/*
		 * [END] Critical CSS
		 */
	}

	/**
	 * @return void
	 */
	public static function chosenScriptInline()
	{
		$chosenScriptInline = <<<JS
jQuery(document).ready(function($) {
    /*
    * [Taxonomies DD]
    * */
    // e.g. for drop-downs such as "Unload CSS on all WooCommerce "Product" pages when the taxonomy (e.g. Category, Tag) has a certain value"
    $('.wpacu_chosen_select.wpacu_manage_via_tax_dd').chosen({'width':'100%'});
    
    // make sure the rest of the chosen drop-downs have the default settings
    $('.wpacu_chosen_select:not(.wpacu_manage_via_tax_dd)').chosen();
    /*
    * [/Taxonomies DD]
    */
    
    /*
    * [Post Types DD]
    */
    $('.wpacu_chosen_select.wpacu_plugin_manage_via_post_type_dd').chosen({'width':'100%'});
    
    // make sure the rest of the chosen drop-downs have the default settings
    $('.wpacu_chosen_select:not(.wpacu_plugin_manage_via_post_type_dd)').chosen();
    /*
    * [/Post Types DD]
    */
    
    /*
    * [Page Types DD]
    */
    $('.wpacu_chosen_select.wpacu_plugin_manage_via_page_type_dd').chosen({'width':'100%'});
    
    // make sure the rest of the chosen drop-downs have the default settings
    $('.wpacu_chosen_select:not(.wpacu_plugin_manage_via_page_type_dd)').chosen();
    /*
    * [/Page Types DD]
    */
});
JS;
		wp_add_inline_script(OwnAssets::$ownAssets['scripts']['chosen']['handle'], $chosenScriptInline);
	}

	/**
	 * This code is called from class: "OwnAssets" - method: "enqueueAdminScripts"
	 *
	 * @return void
	 */
	public static function sweetAlertNotifications()
	{
		/*
		* [START] SweetAlert (Pro features)
		*/
		$wpacuSiteUrl = site_url();

		$wpacuSubPage = (isset($_GET['wpacu_sub_page']) && $_GET['wpacu_sub_page']) ? $_GET['wpacu_sub_page'] : 'manage_plugins_front';

		$textMediaQuery = sprintf(
			esc_js(__('You have added @media in the input box which has been removed. It is not needed here as it not included within a CSS STYLE/LINK tag. For instance, if the CSS media query you had in mind is %s, then you can just input %s.', 'wp-asset-clean-up-pro')),
			'<strong>@media (min-width: 768px)</strong>', '<strong>(min-width: 768px)</strong>'
		);

		$textRegExHasSiteUrlTitle = esc_js(__('I understand', 'wp-asset-clean-up-pro'));
		$textRegExHasSiteUrlMsg   = sprintf(
			esc_js(__( 'You have added the website URL in the input box which has been removed. It is not needed here as only the request URI is required. For instance, if you want the following URL to match %s then you can just use %s as a rule. Also, being relative, it would look cleaner and still work as it should whenever you move from staging to live or vice-versa.', 'wp-asset-clean-up-pro')),
			$wpacuSiteUrl . '<strong>/contact</strong>',
			'<strong>#/contact#</strong>'
		);

		$textRegExHasUrlTitle = esc_js(__('I understand', 'wp-asset-clean-up-pro'));
		$textRegExHasUrlMsg   = sprintf(
			esc_js(__( 'Your RegEx should not start with a URL. Only the request URI is required. For instance, if you want the following URL to match %s then you can just use %s as a rule. Also, being relative, it would look cleaner and still work as it should whenever you move from staging to live or vice-versa.', 'wp-asset-clean-up-pro')),
			$wpacuSiteUrl . '<strong>/contact</strong>',
			'<strong>#/contact#</strong>'
		);

		$textRegExPluginsFrontEndViewTitle   = esc_js(__('Heads up! You might be in the wrong tab', 'wp-asset-clean-up-pro'));
		$textRegExPluginsFrontEndViewConfirm = esc_js(__('I understand', 'wp-asset-clean-up-pro'));
		$textRegExPluginsFrontEndViewMsg     = sprintf(
			esc_js(__('You have added a RegEx rule that contains <strong>wp-admin</strong> to the input box and you are in the area where rules for the frontend view should be added (current tab: \'%s\').<br /><br />If your intention is to apply the rule for an admin page, then you have to access the \'%s /wp-admin/\' tab.', 'wp-asset-clean-up-pro')),
			esc_js(__('IN FRONTEND VIEW (your visitors)', 'wp-asset-clean-up')),
			esc_js(__('IN THE DASHBOARD', 'wp-asset-clean-up'))
		);

		$textPluginLoadUnloadLoggedInConfirm = esc_js(__('I understand', 'wp-asset-clean-up-pro'));
		$textPluginLoadUnloadLoggedInMsg = sprintf(
			esc_js(__('You have marked both %sunload the plugin if the user is logged in%s and to %salways load it if the user is logged in%s which cancel each other. The load exception rules, that are below the unload rules, always have priority. The checkboxes have been ticked off. Please review the unload and load exceptions rules again to avoid any confusion.', 'wp-asset-clean-up-pro')),
			'<strong style=\'color: #c00;\'>', '</strong>',
			'<strong style=\'color: green;\'>', '</strong>'
		);

		$textPluginLoadUnloadHomepageConfirm = esc_js(__('I understand', 'wp-asset-clean-up-pro'));
		$textPluginLoadUnloadHomepageMsg     = sprintf(
			esc_js(__('You have marked both %sunload on the homepage%s and %salways load it on the homepage%s which cancel each other. The load exception rules, that are below the unload rules, always have priority. The checkboxes have been ticked off. Please review the unload and load exceptions rules again to avoid any confusion.', 'wp-asset-clean-up-pro')),
			'<strong style=\'color: #c00;\'>', '</strong>',
			'<strong style=\'color: green;\'>', '</strong>'
		);

		$sweetAlertTwoScriptInline = <<<JS
jQuery(document).ready(function($) {
    $(document).on('change focusout blur', '.wpacu-handle-media-queries-load-field-input', function() {
        //console.log('Media Query Load Input Change');
        if ($(this).val().toLowerCase().indexOf('@media') > -1) {
            $(this).val($(this).val().toLowerCase().replace('@media', ''));
            wpacuSwal.fire({
                icon: "info",
                confirmButtonText: '<i class="fa fa-thumbs-up"></i> {$textRegExHasSiteUrlTitle}',
                html: "{$textMediaQuery}"
            });
         }
    });
    
    $(document).on('change focusout blur', '.wpacu_regex_rule_textarea', function() {
        var wpacuSiteUrl = '{$wpacuSiteUrl}';
        
        //console.log('RegEx Rule Textarea Input Change');
        
        /*
         * This will show an alert if the website URL is added to a RegEx rule
         * for either a handle in the "CSS/JS Manager or a plugin within "Plugins Manager"
         * alerting the user that the relative path - the URI - should be used, not the whole URL
         */
        if ($(this).val().toLowerCase().indexOf(wpacuSiteUrl) > -1) {
            $(this).val($(this).val().toLowerCase().replace(wpacuSiteUrl, ''));
            
            if ($(this).val() === '/') {
                $(this).val(''); // if only a forward slash was left, remove it as it's not relevant
            }
            wpacuSwal.fire({
                width: 600,
                icon: "info",
                confirmButtonText: '<i class="fa fa-thumbs-up"></i> {$textRegExHasSiteUrlTitle}',
                html: "{$textRegExHasSiteUrlMsg}"
            });
        }
        /*
         * This will show an alert if a URL is added to a RegEx rule
         * for either a handle in the "CSS/JS Manager or a plugin within "Plugins Manager"
         * alerting the user that the relative path - the URI - should be used, not the whole URL
         */
        else if ($(this).val().toLowerCase().indexOf('http://') > -1 || $(this).val().toLowerCase().indexOf('https://') > -1) {
            $(this).val($(this).val().toLowerCase().replace('http://', '').replace('https://', ''));
            
            wpacuSwal.fire({
                width: 600,
                icon: "info",
                confirmButtonText: '<i class="fa fa-thumbs-up"></i> {$textRegExHasUrlTitle}',
                html: "{$textRegExHasUrlMsg}"
            });
        }
        
        var wpacuSubPage = '{$wpacuSubPage}';
        
        /*
         * If the user is within "IN FRONTEND VIEW (your visitors)" and adds "wp-admin" as part of the RegEx
         * alert him/her that it's most likely a mistake and he/she could be in the wrong tab, since " IN THE DASHBOARD /wp-admin/"
         * is the right tab for unloading plugins within the Dashboard
         */
        if (wpacuSubPage === 'manage_plugins_front' && $(this).val().toLowerCase().indexOf('wp-admin') > -1) {
            wpacuSwal.fire({
                width: 650,
                icon: "warning",
                title: "{$textRegExPluginsFrontEndViewTitle}",
                confirmButtonText: '<i class="fa fa-thumbs-up"></i> {$textRegExPluginsFrontEndViewConfirm}',
                html: "{$textRegExPluginsFrontEndViewMsg}"
            });
        }
    });

    var wpacuPluginPath, wpacuUnloadLoggedInTarget, wpacuLoadLoggedInTarget, wpacuUnloadHomePageTarget, wpacuLoadHomePageTarget;
    
    $(document).on('change focusout blur', '.wpacu_plugin_unload_logged_in, .wpacu_plugin_load_exception_logged_in', function() {
        wpacuPluginPath = $(this).attr('data-wpacu-plugin-path');
        wpacuUnloadLoggedInTarget = 'input[data-wpacu-plugin-path="'+ wpacuPluginPath +'"].wpacu_plugin_unload_logged_in';
        wpacuLoadLoggedInTarget = 'input[data-wpacu-plugin-path="'+ wpacuPluginPath +'"].wpacu_plugin_load_exception_logged_in';
 
        if ($(wpacuUnloadLoggedInTarget).prop('checked') && $(wpacuLoadLoggedInTarget).prop('checked')) {
           $(wpacuUnloadLoggedInTarget).prop('checked', false);
           $(wpacuLoadLoggedInTarget).prop('checked', false);
           
           wpacuSwal.fire({
                width: 600,
                icon: "info",
                confirmButtonText: '<i class="fa fa-thumbs-up"></i> {$textPluginLoadUnloadLoggedInConfirm}',
                html: "{$textPluginLoadUnloadLoggedInMsg}"
            });
           
           $(document).trigger('wpacu_plugin_row_show_hide_load_exceptions_area', wpacuPluginPath);
           
           return false;
         }
    });
    
    $(document).on('change focusout blur', '.wpacu_plugin_unload_home_page, .wpacu_plugin_load_home_page', function() {
        wpacuPluginPath = $(this).attr('data-wpacu-plugin-path');
        wpacuUnloadHomePageTarget = 'input[data-wpacu-plugin-path="'+ wpacuPluginPath +'"].wpacu_plugin_unload_home_page';
        wpacuLoadHomePageTarget = 'input[data-wpacu-plugin-path="'+ wpacuPluginPath +'"].wpacu_plugin_load_home_page';
 
        if ($(wpacuUnloadHomePageTarget).prop('checked') && $(wpacuLoadHomePageTarget).prop('checked')) {
           $(wpacuUnloadHomePageTarget).prop('checked', false).parent().removeClass('wpacu_plugin_unload_rule_input_checked');
           $(wpacuLoadHomePageTarget).prop('checked', false).parent().removeClass('wpacu_plugin_unload_rule_input_checked');
               
           wpacuSwal.fire({
                width: 600,
                icon: "info",
                confirmButtonText: '<i class="fa fa-thumbs-up"></i> {$textPluginLoadUnloadHomepageConfirm}',
                html: "{$textPluginLoadUnloadHomepageMsg}"
            });
           
           $(document).trigger('wpacu_plugin_row_show_hide_load_exceptions_area', wpacuPluginPath);
           
           return false;
         }
    });
});
JS;
		wp_add_inline_script(OwnAssets::$ownAssets['scripts']['sweetalert2']['handle'], $sweetAlertTwoScriptInline);
		/*
		 * [END] SweetAlert (Pro features)
		 */
	}
}