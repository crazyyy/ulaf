<?php

use ScssPhp\ScssPhp\Exception\SassException;
use YahnisElsts\AdminMenuEditor\AdminCustomizer\AcAdminColorSchemeData;
use YahnisElsts\AdminMenuEditor\Customizable\Builders\ElementBuilderFactory;
use YahnisElsts\AdminMenuEditor\Customizable\Rendering\BoxyFormRenderer;
use YahnisElsts\AdminMenuEditor\Customizable\Settings;
use YahnisElsts\AdminMenuEditor\Customizable\SettingsForm;
use YahnisElsts\AdminMenuEditor\Customizable\Storage\ModuleSettings;

require_once 'ameAdminColorScheme.php';

class ameBrandingColors extends amePersistentProModule {
	const PREVIEW_ACTION = 'ame_branding_preview_colors';
	const PREVIEW_CACHING_PARAM = 'ame_branding_color_caching';
	const PREVIEW_CACHE_PREFIX = 'ws_ame_admin_color_preview-';

	const CUSTOM_SCHEME_ID = 'ame-branding-colors';
	const CUSTOM_SCHEME_NAME = 'Custom (AME)';
	const CUSTOM_SCHEME_ACTION = 'ame_branding_output_custom_colors';
	const CSS_CACHE_OPTION = 'ws_ame_admin_color_scheme_css';

	const ADMIN_BAR_CSS_ACTION = 'ame_branding_output_admin_bar_css';

	protected $tabSlug = 'colors';
	protected $tabTitle = 'Colors';

	protected $settingsFormAction = 'ame-save-branding-colors';
	/**
	 * @var null|\YahnisElsts\AdminMenuEditor\Customizable\SettingsForm
	 */
	protected $form = null;

	protected $optionName = 'ws_ame_admin_colors';
	protected $defaultSettings = [
		'is_color_override_enabled' => true,
	];

	private $cachedColorScheme = null;

	public function __construct($menuEditor) {
		$this->settingsWrapperEnabled = true;
		parent::__construct($menuEditor);

		add_action('admin_init', [$this, 'applyColorScheme']);

		add_action('wp_ajax_' . self::CUSTOM_SCHEME_ACTION, [$this, 'outputCustomColorScheme']);
		add_action('wp_ajax_' . self::PREVIEW_ACTION, [$this, 'outputPreviewColorScheme']);
		add_action('wp_ajax_' . self::ADMIN_BAR_CSS_ACTION, [$this, 'outputAdminBarCss']);

		add_action('wp_enqueue_scripts', [$this, 'enqueueAdminBarStyle']);

		add_action('admin_menu_editor-register_ac_items', [$this, 'registerAdminCustomizerItems']);
		add_action('admin_menu_editor-ac_admin_theme_color_scheme', [$this, 'getColorSchemeForAdminTheme']);
	}

	public function createSettingInstances(ModuleSettings $settings) {
		$f = $settings->settingFactory();

		$schemeSetting = $f->customStruct(
			'scheme',
			null,
			['deleteWhenBlank' => true]
		);

		$schemeSetting->createChild(
			'hash',
			Settings\StringSetting::class,
			[
				'maxLength'       => 32,
				'deleteWhenBlank' => true,
				'tags'            => [Settings\AbstractSetting::TAG_ADMIN_THEME],
			]
		);
		/**
		 * @var Settings\UserDefinedStruct $colorSettings
		 */
		$colorSettings = $schemeSetting->createChild(
			'colors',
			Settings\UserDefinedStruct::class,
			['deleteWhenBlank' => true]
		);
		foreach (ameAdminColorScheme::getAvailableOptions() as $key => $label) {
			$colorSettings->createChild(
				$key,
				Settings\ColorSetting::class,
				[
					'label'           => $label,
					'deleteWhenBlank' => true,
					'tags'            => [Settings\AbstractSetting::TAG_ADMIN_THEME],
				]
			);
		}

		$colorSettings->subscribe(function () use ($schemeSetting) {
			//Are there any non-empty colors?
			$values = $schemeSetting->getValue();
			$validColors = array_filter(ameUtils::get($values, 'colors', []), function ($color) {
				return !empty($color);
			});

			if ( empty($validColors) ) {
				$schemeSetting->getChild('hash')->update(null);
			} else {
				//Colors should already be validated at this point.
				$scheme = new ameAdminColorScheme($validColors);
				$this->updateCssCache($scheme);
				$schemeSetting->getChild('hash')->update($scheme->getHash());
			}
		});

		$results = [$schemeSetting];

		$booleanSettings = [
			'is_color_override_enabled'    => 'Apply this color scheme to all users',
			'are_advanced_options_visible' => 'Show advanced options',
			'is_live_preview_enabled'      => 'Live preview (may be slow)',
		];
		foreach ($booleanSettings as $setting => $label) {
			$results[] = $f->boolean($setting, $label, ['tags' => [Settings\AbstractSetting::TAG_ADMIN_THEME]]);
		}

		return $results;
	}

	protected function getInterfaceStructure() {
		$settings = $this->loadSettings();
		$b = $settings->elementBuilder();

		$structure = $b->structure();

		//Color settings
		$basics = array_fill_keys([
			'base-color',
			'text-color',
			'highlight-color',
			'icon-color',
			'notification-color',
		], true);

		$colorSection = $b->section()->id('color-scheme-section');
		foreach ($this->buildColorPickers($b) as $key => $picker) {
			$group = $picker->asGroup();
			if ( !isset($basics[$key]) ) {
				$group->classes('ame-advanced-admin-color');
			}
			$colorSection->add($group);
		}
		$structure->add($colorSection);

		//Sidebar
		$structure->add(
			$b->section()
				->id('sidebar')
				->add(
					$b->group(
						'',
						$b->saveButton(),
						$b->html(
							'<input id="ame-color-preview-button" class="button" value="Preview" type="button" style="float: right;">'
							. '<span class="spinner" style="float: right;" id="ame-color-preview-in-progress"></span>'
						)
					),

					$b->auto('is_color_override_enabled')
						->asGroup()->classes('ame-ac-is_color_override_enabled'),
					$b->auto('are_advanced_options_visible')
						->asGroup()->classes('ame-ac-are_advanced_options_visible'),
					$b->auto('is_live_preview_enabled')
						->asGroup()->classes('ame-ac-is_live_preview_enabled')
				)
		);

		return $structure->build();
	}

	/**
	 * @param \YahnisElsts\AdminMenuEditor\Customizable\Builders\ElementBuilderFactory $b
	 * @return \YahnisElsts\AdminMenuEditor\Customizable\Builders\ControlBuilder[]
	 */
	private function buildColorPickers(ElementBuilderFactory $b) {
		$colorPickers = [];

		foreach ($this->extractColorSettings($b->getSettingDictionary()) as $key => $setting) {
			$colorPickers[$key] = $b->colorPicker($setting)
				->inputAttr(['data-color-variable' => $key]);
		}

		return $colorPickers;
	}

	protected function getSettingsForm() {
		if ( $this->form === null ) {
			$this->form = SettingsForm::builder($this->settingsFormAction)
				->settings($this->loadSettings()->getRegisteredSettings())
				->structure($this->getInterfaceStructure())
				->submitUrl($this->getTabUrl(['noheader' => 1]))
				->redirectAfterSaving($this->getTabUrl(['updated' => 1]))
				->treatMissingFieldsAsEmpty()
				->addDefaultSubmitButton(false)
				->renderer(new BoxyFormRenderer())
				->build();
		}
		return $this->form;
	}

	protected function outputMainTemplate() {
		$settings = $this->loadSettings();

		$wrapperClasses = [];
		if ( ameUtils::get($settings, 'are_advanced_options_visible', false) ) {
			$wrapperClasses[] = 'ame-advanced-options-visible';
		}
		printf(
			'<div id="ame-admin-colors-wrapper" class="%s">',
			esc_attr(implode($wrapperClasses))
		);

		$this->getSettingsForm()->output();

		echo '</div>';
		return true;
	}

	public function handleSettingsForm($post = []) {
		$this->cachedColorScheme = null;

		$this->getSettingsForm()->handleUpdateRequest($post);
		exit;
	}

	/**
	 * @param ameAdminColorScheme $scheme
	 * @return void
	 * @throws \ScssPhp\ScssPhp\Exception\SassException
	 */
	protected function updateCssCache($scheme) {
		try {
			$this->setScopedOption(
				self::CSS_CACHE_OPTION,
				[
					'color-scheme' => $scheme->compileToCss(),
					'admin-bar'    => $scheme->compileAdminBarStylesToCss(),
				],
				'no'
			);
		} catch (SassException $ex) {
			$this->setScopedOption(self::CSS_CACHE_OPTION, null, 'no');
			//Rethrow to let the user know that something is wrong.
			throw $ex;
		}
	}

	/**
	 * @param \YahnisElsts\AdminMenuEditor\AdminCustomizer\AmeAdminCustomizer $adminCustomizer
	 * @return void
	 */
	public function registerAdminCustomizerItems($adminCustomizer) {
		$settings = $this->loadSettings();
		$adminCustomizer->addSettings($settings->getRegisteredSettings());

		//Enable postMessage support for all color settings.
		foreach ($this->extractColorSettings($settings) as $setting) {
			$adminCustomizer->enablePostMessage($setting);
		}
		//Also enable postMessage support for the override setting.
		$adminCustomizer->enablePostMessage($settings->findSetting('is_color_override_enabled'));

		//Set up the UI for the "Color Scheme" section.
		$b = $settings->elementBuilder();
		$colorSchemeSection = $b->section('Color Scheme')->id('ame-branding-color-scheme');

		$colorSchemeSection->add(
			$b->auto('is_color_override_enabled')
		);

		foreach ($this->buildColorPickers($b) as $picker) {
			$colorSchemeSection->add($picker);
		}

		$adminCustomizer->addSection($colorSchemeSection->build());

		add_action(
			'admin_menu_editor-enqueue_ac_dependencies',
			[$this, 'enqueueAdminCustomizerDependencies']
		);
		add_action(
			'admin_menu_editor-enqueue_ac_preview',
			[$this, 'enqueueAdminCustomizerPreview']
		);
	}

	/**
	 * @param \YahnisElsts\AdminMenuEditor\Customizable\Storage\AbstractSettingsDictionary $settingDictionary
	 * @return \Iterator<string,Settings\AbstractSetting>
	 */
	private function extractColorSettings($settingDictionary = null) {
		if ( $settingDictionary === null ) {
			$settingDictionary = $this->loadSettings();
		}
		foreach (array_keys(ameAdminColorScheme::getAvailableOptions()) as $key) {
			$setting = $settingDictionary->findSetting('scheme.colors.' . $key);
			if ( $setting !== null ) {
				yield $key => $setting;
			}
		}
	}

	public function enqueueAdminCustomizerDependencies() {
		wp_enqueue_auto_versioned_script(
			'ame-ac-admin-colors-helper',
			plugins_url('ac-color-scheme-helper.js', __FILE__),
			['jquery']
		);
	}

	public function enqueueAdminCustomizerPreview() {
		wp_enqueue_auto_versioned_script(
			'ame-ac-admin-colors-preview',
			plugins_url('ac-color-scheme-preview.js', __FILE__),
			['jquery', 'lodash']
		);

		$overrideSetting = $this->loadSettings()->findSetting('is_color_override_enabled');

		$url = $this->getPreviewBaseUrl();
		$scriptData = [
			'colorVariableOrder' => array_flip(ameAdminColorScheme::getColorListOrder()),
			'previewBaseUrl'     => $url,
			'isOverrideEnabled'  => $overrideSetting ? $overrideSetting->getValue() : false,
			'overrideSettingId'  => $overrideSetting ? $overrideSetting->getId() : null,
			'forceEnablePreview' => false,
		];

		//Add color setting IDs and settings values to script data.
		$settingData = [];
		foreach ($this->extractColorSettings($this->settings) as $variableName => $setting) {
			$settingData[$setting->getId()] = [
				'initialValue' => $setting->getValue(),
				'variableName' => $variableName,
			];
		}

		$scriptData['colorSettings'] = $settingData;

		wp_add_inline_script(
			'ame-ac-admin-colors-preview',
			'var wsAmeBrandingClsPreviewData = (' . wp_json_encode($scriptData) . ');',
			'before'
		);
	}

	public function importSettings($newSettings) {
		parent::importSettings($newSettings);

		//Generate CSS for the imported color scheme.
		if ( isset($this->settings) && !empty($this->settings['scheme']) ) {
			$scheme = ameAdminColorScheme::fromArray($this->settings['scheme']);
			/**
			 * @noinspection PhpUnhandledExceptionInspection
			 * If there's an exception, I want to know about it since it could
			 * indicate a bug in the plugin. Let it crash.
			 */
			$this->updateCssCache($scheme);
		}
	}


	public function enqueueTabScripts() {
		parent::enqueueTabScripts();

		wp_enqueue_auto_versioned_script(
			'ame-branding-color-settings',
			plugins_url('modules/admin-colors/admin-colors.js', AME_BRANDING_ADD_ON_FILE),
			['jquery', 'ame-lodash']
		);

		wp_localize_script(
			'ame-branding-color-settings',
			'wsAmeBrandingColorData',
			[
				'previewBaseUrl'       => $this->getPreviewBaseUrl(),
				'colorOrderForPreview' => array_flip(ameAdminColorScheme::getColorListOrder()),
			]
		);
	}

	private function getPreviewBaseUrl() {
		//Note: wp_nonce_url() is not used here because it runs the URL through
		//esc_html() which replaces "&" with "&amp;".
		return add_query_arg(
			[
				'action'   => self::PREVIEW_ACTION,
				'_wpnonce' => wp_create_nonce(self::PREVIEW_ACTION),
			],
			admin_url('admin-ajax.php')
		);
	}

	public function enqueueTabStyles() {
		parent::enqueueTabStyles();

		wp_enqueue_auto_versioned_style(
			'admin-branding-color-settings-css',
			plugins_url('modules/admin-colors/admin-colors.css', AME_BRANDING_ADD_ON_FILE)
		);
	}

	public function applyColorScheme() {
		$scheme = $this->getCustomColorScheme();
		if ( ($scheme === null) || !$scheme->hasCustomColors() ) {
			return;
		}

		$isPreview = $this->isCustomizerPreview();
		if ( $isPreview ) {
			//In the preview frame, the cached stylesheet might not match the previewed
			//colors. We'll generate a separate preview stylesheet, with short-term caching.
			$stylesheetUrl = add_query_arg(
				[self::PREVIEW_CACHING_PARAM => 1],
				$this->getPreviewStylesheetUrl($scheme)
			);
		} else {
			//The network admin and user admin don't have their own admin-ajax.php, so self_admin_url()
			//won't work here. We have to use the regular /wp-admin/admin-ajax.php file for all dashboards.
			$adminAjaxUrl = admin_url('admin-ajax.php');

			$stylesheetUrl = add_query_arg(
				[
					'action' => self::CUSTOM_SCHEME_ACTION,
					'hash'   => $scheme->getHash(),
				],
				$adminAjaxUrl
			);
		}

		//Register the color scheme.
		wp_admin_css_color(
			self::CUSTOM_SCHEME_ID,
			self::CUSTOM_SCHEME_NAME,
			$stylesheetUrl,
			$scheme->getDemoColors(),
			$scheme->getSvgIconColors()
		);

		$isOverrideEnabled = ameUtils::get($this->settings, 'is_color_override_enabled', false);
		if ( $isOverrideEnabled ) {
			//Remove the "Admin Color Scheme" setting from the "Profile" page.
			remove_action('admin_color_scheme_picker', 'admin_color_scheme_picker');
			//Force everyone to use the custom color scheme.
			add_filter('get_user_option_admin_color', [$this, 'overrideUserColorScheme']);
		}
	}

	private function getCustomColorScheme() {
		if ( isset($this->cachedColorScheme) ) {
			return $this->cachedColorScheme;
		}
		$this->loadSettings();
		if ( empty($this->settings['scheme']) ) {
			return null;
		}

		$this->cachedColorScheme = ameAdminColorScheme::fromArray($this->settings['scheme']);
		return $this->cachedColorScheme;
	}

	public function outputCustomColorScheme() {
		$cache = $this->getScopedOption(self::CSS_CACHE_OPTION);
		if ( is_array($cache) && isset($cache['color-scheme']) ) {
			$css = $cache['color-scheme'];
		} else if ( is_string($cache) ) {
			//For backwards compatibility. Older versions stored only the color scheme CSS, not admin bar CSS.
			$css = $cache;
		} else {
			echo '/* Error: There is no custom color scheme. */';
			exit;
		}

		$this->sendCssHeaders();
		echo $css;
		exit;
	}

	public function overrideUserColorScheme() {
		return self::CUSTOM_SCHEME_ID;
	}

	public function outputPreviewColorScheme() {
		$startTime = microtime(true);

		check_ajax_referer(self::PREVIEW_ACTION);
		if ( !$this->userCanAccessColorPreview() ) {
			exit('Error: You don\'t have permission to change admin color settings.');
		}

		if ( !isset($_GET['colors']) || !is_string($_GET['colors']) ) {
			exit('Error: No colors specified');
		}

		$scheme = new ameAdminColorScheme(explode('.', $_GET['colors']));

		//Optimization: In the customizer preview, the stylesheet can be cached.
		$cachingEnabled = !empty($_GET[self::PREVIEW_CACHING_PARAM]);
		$cacheKey = $cachingEnabled ? (self::PREVIEW_CACHE_PREFIX . $scheme->getHash()) : '';
		$isCachePopulated = false;

		if ( $cachingEnabled ) {
			$css = get_transient(self::PREVIEW_CACHE_PREFIX . $scheme->getHash());
			if ( !empty($css) ) {
				$isCachePopulated = true;
			}
		}

		if ( empty($css) ) {
			/** @noinspection PhpUnhandledExceptionInspection */
			$css = $scheme->compileToCss();
		}

		$this->sendCssHeaders('+1 hour');
		echo $css;

		echo sprintf('/* Generated in %s seconds. */', number_format(microtime(true) - $startTime, 3));

		if ( $cachingEnabled && !$isCachePopulated ) {
			set_transient($cacheKey, $css, 30 * 60);
		}
		exit;
	}

	private function userCanAccessColorPreview() {
		if ( $this->menuEditor->current_user_can_edit_menu() ) {
			return true;
		}

		//Alternatively, the user needs access to the Admin Customizer interface.
		//They may not be able to actually change the underlying settings, but they
		//can still preview changesets.
		$loadedModules = $this->menuEditor->get_loaded_modules();
		if (
			!empty($loadedModules['admin-customizer'])
			&& ($loadedModules['admin-customizer'] instanceof ameModule)
		) {
			$adminCustomizer = $loadedModules['admin-customizer'];
			if (
				method_exists($adminCustomizer, 'userCanAccessModule')
				&& is_callable([$adminCustomizer, 'userCanAccessModule'])
			) {
				return $adminCustomizer->userCanAccessModule();
			}
		}

		return false;
	}

	/**
	 * @param \ameAdminColorScheme $scheme
	 * @return string
	 */
	private function getPreviewStylesheetUrl($scheme = null) {
		$url = $this->getPreviewBaseUrl();

		if ( $scheme !== null ) {
			//Convert the color scheme to a dot-separated string.
			$colorSettings = $scheme->getColors();
			$colorList = [];
			foreach (ameAdminColorScheme::getColorListOrder() as $index => $colorName) {
				if ( !empty($colorSettings[$colorName]) ) {
					$colorList[$index] = str_replace('#', '', $colorSettings[$colorName]);
				} else {
					$colorList[$index] = '';
				}
			}
			$colorString = implode('.', $colorList);

			$url = add_query_arg('colors', $colorString, $url);
		}

		return $url;
	}

	private function isCustomizerPreview() {
		return apply_filters('admin_menu_editor-is_preview_frame', false);
	}

	public function enqueueAdminBarStyle() {
		//Only logged-in users can see the admin bar.
		if ( !is_user_logged_in() ) {
			return;
		}

		//Should we use the custom color scheme for this user?
		$this->loadSettings();
		$isEnabled = ameUtils::get($this->settings, 'is_color_override_enabled', false)
			|| (get_user_option('admin_color') === self::CUSTOM_SCHEME_ID);
		if ( !$isEnabled ) {
			return;
		}

		$scheme = $this->getCustomColorScheme();
		if ( ($scheme === null) || !$scheme->hasCustomColors() ) {
			return;
		}

		if ( $this->isCustomizerPreview() ) {
			//For simplicity, just load the full preview stylesheet
			//instead of only the Admin Bar portion.
			$stylesheetUrl = $this->getPreviewStylesheetUrl($scheme);
		} else {
			$stylesheetUrl = add_query_arg(
				[
					'action' => self::ADMIN_BAR_CSS_ACTION,
					'hash'   => $scheme->getHash(),
				],
				self_admin_url('admin-ajax.php')
			);
		}

		wp_enqueue_style(self::CUSTOM_SCHEME_ID . '-admin-bar', $stylesheetUrl);
	}

	public function outputAdminBarCss() {
		$cache = $this->getScopedOption(self::CSS_CACHE_OPTION);
		if ( !is_array($cache) || !isset($cache['admin-bar']) ) {
			echo '/* Error: There is no cached CSS for the Admin Bar. */';
			exit;
		}

		$this->sendCssHeaders();
		echo $cache['admin-bar'];
		exit;
	}

	private function sendCssHeaders($cacheExpires = '+1 year') {
		header('Content-Type: text/css');
		header('X-Content-Type-Options: nosniff');

		if ( $cacheExpires ) {
			//Enable browser caching.
			header('Cache-Control: public');
			header('Pragma: cache');
			/** @noinspection PhpRedundantOptionalArgumentInspection */
			header('Expires: ' . gmdate('D, d M Y H:i:s T', strtotime($cacheExpires)), true);
		}
	}

	/**
	 * @param null|AcAdminColorSchemeData $defaultResult
	 * @return null|AcAdminColorSchemeData
	 * @internal
	 */
	public function getColorSchemeForAdminTheme($defaultResult = null) {
		if ( !class_exists(AcAdminColorSchemeData::class) ) {
			return $defaultResult;
		}

		$scheme = $this->getCustomColorScheme();
		if ( ($scheme === null) || !$scheme->hasCustomColors() ) {
			return $defaultResult;
		}

		//Convert demo colors from a plain array to an associative array.
		$demoColors = $scheme->getDemoColors();
		$demoKeys = ['base', 'icon', 'notification', 'highlight'];
		$demoColorsByKey = [];
		foreach ($demoColors as $i => $color) {
			if ( isset($demoKeys[$i]) ) {
				$demoColorsByKey[$demoKeys[$i]] = $color;
			}
		}

		try {
			return new AcAdminColorSchemeData(
				$scheme->compileToCss(),
				$scheme->compileAdminBarStylesToCss(),
				[
					'demo'                   => $demoColorsByKey,
					'icons'                  => $scheme->getSvgIconColors(),
					'isColorOverrideEnabled' => ameUtils::get($this->settings, 'is_color_override_enabled', false),
				]
			);
		} catch (SassException $e) {
			return $defaultResult;
		}
	}
}