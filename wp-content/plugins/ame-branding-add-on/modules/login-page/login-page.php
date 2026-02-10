<?php

use YahnisElsts\AdminMenuEditor\Customizable\Settings;
use YahnisElsts\AdminMenuEditor\Customizable\Settings\ImageSetting;
use YahnisElsts\AdminMenuEditor\Customizable\SettingsForm;
use YahnisElsts\AdminMenuEditor\Customizable\Storage\ModuleSettings;
use YahnisElsts\AdminMenuEditor\ProCustomizable\Settings\Background;

class ameLoginPageCustomizer extends amePersistentProModule {
	protected $optionName = 'ws_ame_login_page_settings';

	protected $tabTitle = 'Login';
	protected $tabSlug = 'login-page';

	protected $settingsFormAction = 'ame_save_login_page_settings';

	/**
	 * @var SettingsForm|null
	 */
	protected $form = null;

	protected static $hideableOptions = array(
		'register_link_enabled'      => 'Registration link',
		'back_to_link_enabled'       => '"Back to [site name]" link',
		'lost_password_link_enabled' => '"Lost your password?" link',
	);

	public function __construct($menuEditor) {
		$this->settingsWrapperEnabled = true;
		parent::__construct($menuEditor);

		if ( !$this->isEnabledForRequest() ) {
			return;
		}

		add_action('login_init', array($this, 'registerLoginHooks'));

		require_once(AME_BRANDING_ADD_ON_DIR . '/includes/ameBrandingHideableHelper.php');
		$me = $this;
		new ameBrandingHideableHelper(
			$this,
			self::$hideableOptions,
			'login/',
			/**
			 * @param \YahnisElsts\AdminMenuEditor\EasyHide\HideableItemStore $store
			 * @return \YahnisElsts\AdminMenuEditor\EasyHide\Category
			 */
			function ($store) {
				return $store->getOrCreateCategory('br-login', 'Login Page', null, true);
			},
			function ($settings) use ($me) {
				//TODO: Test if this still works with ModuleSettings. It looks like it should.
				$me->settings = $settings;
				$me->saveSettings();
			},
			10,
			null,
			true
		);
	}

	public function createSettingInstances(ModuleSettings $settings) {
		$f = $settings->settingFactory();

		return array(
			new Settings\ImageSetting(
				$f->getIdPrefix() . 'logo_image',
				$settings->getStore()->buildSlot('logo_image')
			),

			$f->url('logo_link_url', 'Logo link URL', ['default' => '']),
			$f->string('logo_title_text', 'Title text'),
			$f->string('page_title', 'Login page title'),

			new Background(
				$f->getIdPrefix() . 'page_background',
				$settings->getStore()->buildSlot('page_background')
			),

			$f->cssColor(
				'login_form_background_color',
				'background-color',
				'Background color'
			),

			$f->userText(
				'custom_login_message',
				'Custom message',
				['default' => '']
			),
			$f->userText(
				'form_top_message',
				'Message above the fields',
				['default' => '']
			),
			$f->userText(
				'form_bottom_message',
				'Message below the fields',
				['default' => '']
			),

			$f->boolean(
				'register_link_enabled',
				'Show the registration link',
				['default' => true]
			),
			$f->boolean(
				'back_to_link_enabled',
				'Show the "Back to [site name]" link',
				['default' => true]
			),
			$f->boolean(
				'lost_password_link_enabled',
				'Show the "Lost your password?" link',
				['default' => true]
			),

			$f->userText('custom_css', 'Custom CSS', ['default' => '']),
			$f->userText('custom_js', 'Custom JS', ['default' => '']),

			$f->string(
				'login_alias',
				'Slug',
				['regex' => '/^[a-z0-9_\-]{1,60}$/']
			),
		);
	}

	protected function getSettingAliases() {
		//For backward compatibility with older versions of the plugin,
		//some settings can be read from the old setting names.
		return array(
			'logo_image.attachmentId'  => 'logo_image_attachment_id',
			'logo_image.externalUrl'   => 'logo_image_external_url',
			'logo_image.attachmentUrl' => 'logo_image_attachment_details.0',
			'logo_image.width'         => 'logo_image_attachment_details.1',
			'logo_image.height'        => 'logo_image_attachment_details.2',

			'page_background.color'               => 'page_background_color',
			'page_background.image.attachmentId'  => 'page_background_image_attachment_id',
			'page_background.image.externalUrl'   => 'page_background_image_external_url',
			'page_background.image.attachmentUrl' => 'page_background_image_attachment_details.0',
			'page_background.image.width'         => 'page_background_image_attachment_details.1',
			'page_background.image.height'        => 'page_background_image_attachment_details.2',
			'page_background.repeat'              => 'page_background_repeat',
			'page_background.position'            => 'page_background_position',
			'page_background.size'                => 'page_background_size',
		);
	}

	protected function getInterfaceStructure() {
		$settings = $this->loadSettings();
		$b = $settings->elementBuilder();
		$structure = $b->structure(
			$b->section(
				'Logo',
				$b->auto('logo_image')
					//Technically, a user who can't access the Media Library could
					//still set an external image, but we'll disable the whole field
					//for back-compat with older add-on versions that did so.
					->enabled(current_user_can('upload_files'))
					->asGroup('Logo image'),
				$b->auto('logo_link_url')
					->description(function () {
						if ( is_multisite() ) {
							return 'Defaults to the network home URL: <code>' . esc_html(network_home_url()) . '</code>.';
						} else {
							return sprintf('Defaults to <code>%s</code>.', __('https://wordpress.org/'));
						}
					})
					->asGroup(),
				$b->auto('logo_title_text')
					->description(function () {
						if ( is_multisite() ) {
							$result = 'Defaults to the network name';
							if ( function_exists('get_network') ) {
								$result .= ': "' . esc_html(get_network()->site_name) . '"';
							}
							$result .= '.';
							return $result;
						} else {
							return sprintf('Defaults to "%s".', esc_html(__('Powered by WordPress')));
						}
					})
					->asGroup()
			),
			$b->section(
				'Background',
				$settings->getPredefinedSet('page_background')->createControls($b)
			),
			$b->section(
				'Login Form',
				$b->auto('login_form_background_color')->asGroup(),
				$b->codeEditor('custom_login_message')
					->htmlMode()
					->description(
						'Enter a custom message that will be displayed above the login form. HTML is allowed.
				Example: <code>' . esc_html('<p class="message">Hello World</p>') . '</code>'
					)
					->asGroup(),
				$b->codeEditor('form_top_message')
					->htmlMode()
					->description(
						'Enter a message that will be displayed at the top of the form, '
						. 'just above the username field.'
					)
					->asGroup(),
				$b->codeEditor('form_bottom_message')
					->htmlMode()
					->description(
						'Enter a message that will be displayed at the bottom of form.'
					)
					->asGroup()
			),
			$b->section(
				'Links',
				$b->auto('register_link_enabled')
					->asGroup('Register')
					->onlyIf(get_option('users_can_register')),
				$b->auto('back_to_link_enabled')->asGroup('Back'),
				$b->auto('lost_password_link_enabled')->asGroup('Lost Password')
			)->tooltip(
				"Hiding a link does not prevent people from visiting the corresponding page. "
				. "It's only a cosmetic change."
			),
			$b->section(
				'Custom CSS/JS',
				$b->codeEditor('custom_css')
					->cssMode()
					->description('The CSS will be added to the login page header.')
					->asGroup(),
				$b->codeEditor('custom_js')
					->jsMode()
					->description('The JavaScript will be added to the login page footer.')
					->asGroup()
			),
			$b->section(
				'Other',
				$b->auto('page_title')
					->description('Leave empty to use the default page title.')
					->asGroup()
			),
			$b->section(
				'Login URL Alias',
				$b->auto('login_alias')
					->description(sprintf(
						'Enter an alternative URL for the login page.
				 For example, setting this to <code>sign-in</code> will make 
				 it possible to log in at <code>%s</code>.',
						esc_html(site_url('sign-in', 'login'))
					))
					->asGroup('Slug')
			)->onlyIf(false /* Not implemented */)
		);

		return $structure->build();
	}

	protected function getSettingsForm() {
		if ( $this->form === null ) {
			$this->form = SettingsForm::builder('ame_save_login_page_settings')
				->settings($this->loadSettings()->getRegisteredSettings())
				->structure($this->getInterfaceStructure())
				->submitUrl($this->getTabUrl(array('noheader' => 1)))
				->redirectAfterSaving($this->getTabUrl(array('updated' => 1)))
				->treatMissingFieldsAsEmpty()
				->build();
		}
		return $this->form;
	}

	protected function outputMainTemplate() {
		$this->getSettingsForm()->output();
		return true;
	}

	public function handleSettingsForm($post = array()) {
		parent::handleSettingsForm($post);

		$this->getSettingsForm()->handleUpdateRequest($post);
	}

	public function registerLoginHooks() {
		$this->loadSettings();

		$titleFilter = 'login_headertext';
		$wpVersion = isset($GLOBALS['wp_version']) ? $GLOBALS['wp_version'] : '5.2.1';
		if ( version_compare($wpVersion, '5.2', '<') ) {
			//This filter was deprecated in WP 5.2.
			$titleFilter = 'login_headertitle';
		}

		add_filter('login_headerurl', array($this, 'filterLogoLinkUrl'), 200);
		add_filter($titleFilter, array($this, 'filterLogoTitleText'));
		add_filter('login_message', array($this, 'filterFormMessage'));
		add_filter('register', array($this, 'filterRegistrationLink'));
		add_filter('login_title', array($this, 'filterPageTitle'), 10, 1);

		add_action('login_head', array($this, 'printLoginStyles'), 15);

		add_action('login_enqueue_scripts', array($this, 'enqueueLoginPageScripts'));
		add_action('login_header', array($this, 'printInternalFormMessages'));

		add_action('login_footer', array($this, 'printCustomJs'));
	}

	public function filterLogoLinkUrl($url) {
		$customUrl = ameUtils::get($this->settings, 'logo_link_url', '');
		if ( ($customUrl !== '') && isset($customUrl) ) {
			return $customUrl;
		}
		return $url;
	}

	public function filterLogoTitleText($title) {
		$customTitle = ameUtils::get($this->settings, 'logo_title_text', '');
		if ( ($customTitle !== '') && isset($customTitle) ) {
			//It looks like WordPress doesn't sanitize the text before output,
			//so we'll do it here.
			return esc_html($customTitle);
		}
		return $title;
	}

	public function filterFormMessage($message) {
		global $action;
		if ( isset($action) && ($action !== 'login') ) {
			return $message;
		}

		$customMessage = trim(strval(ameUtils::get($this->settings, 'custom_login_message', '')));
		if ( !empty($customMessage) && empty($message) ) {
			$message = $customMessage;
		}
		return $message;
	}

	public function filterRegistrationLink($linkHtml) {
		if ( !ameUtils::get($this->settings, 'register_link_enabled', true) ) {
			return '';
		}
		return $linkHtml;
	}

	public function filterPageTitle($fullTitle) {
		/** @noinspection PhpRedundantOptionalArgumentInspection Intentionally using NULL in case the default ever changes. */
		$customTitle = ameUtils::get($this->settings, 'page_title', null);
		if ( !is_string($customTitle) || ($customTitle === '') ) {
			return $fullTitle;
		}
		return strip_tags($customTitle);
	}

	public function printLoginStyles() {
		$styles = array();

		if ( $this->settings instanceof ModuleSettings ) {
			/** @var ImageSetting $logoImageSetting */
			$logoImageSetting = $this->settings->getSetting('logo_image');
			$logoImage = $logoImageSetting->getImage();

			//Backwards compatibility:
			if ( empty($logoImage['width']) && empty($logoImage['height']) ) {
				//Try "logo_image_width" and "logo_image_height" instead. Previous versions
				//used these keys to store the size of an external image.
				$logoImage['width'] = ameUtils::get($this->settings, 'logo_image_width', 0);
				$logoImage['height'] = ameUtils::get($this->settings, 'logo_image_height', 0);
			}
		} else {
			$logoImage = array();
		}

		//Logo.
		if ( !empty($logoImage) && !empty($logoImage['url']) ) {
			$styles[] = sprintf(
				'.login h1 a, 
				 .login .wp-login-logo a {
					background-image: none, url("%1$s");
					width: %2$dpx;
					height: %3$dpx;
					background-size: auto;
					background-repeat: no-repeat;
				}',
				$this->escapeUrlForCss($logoImage['url']),
				$logoImage['width'],
				$logoImage['height']
			);
		}

		//Page background color.
		$backgroundColor = ameUtils::get($this->settings, 'page_background.color', '');
		if ( !empty($backgroundColor) ) {
			$styles[] = sprintf(
				'body { background-color: %1$s; }', htmlspecialchars($backgroundColor)
			);
		}

		//Page background image.
		/** @var ImageSetting $backgroundImageSetting */
		$backgroundImageSetting = $this->settings->getSetting('page_background.image');
		$backgroundUrl = $backgroundImageSetting->getImageUrl();
		if ( !empty($backgroundUrl) ) {
			$styles[] = sprintf(
				'body {
					background-image: url("%1$s");
					background-repeat: %2$s;
					background-position: %3$s;
					background-size: %4$s;
				}',
				$this->escapeUrlForCss($backgroundUrl),
				ameUtils::get($this->settings, 'page_background.repeat', 'repeat'),
				ameUtils::get($this->settings, 'page_background.position', 'left top'),
				ameUtils::get($this->settings, 'page_background.size', 'auto')
			);
		}

		//Form background color.
		$formBackgroundColor = ameUtils::get($this->settings, 'login_form_background_color', '');
		if ( !empty($formBackgroundColor) ) {
			$styles[] = sprintf(
				'.login form { background-color: %1$s; }',
				htmlspecialchars($formBackgroundColor)
			);
		}

		//Messages above and below the form fields.
		$topMessage = ameUtils::get($this->settings, 'form_top_message', '');
		$bottomMessage = ameUtils::get($this->settings, 'form_bottom_message', '');
		if ( !empty($topMessage) || !empty($bottomMessage) ) {
			$styles[] = '#ame-login-form_top_message, #ame-login-form_bottom_message { 
				clear: both;
				font-size: 14px; 
			}';
			$styles[] = '#ame-login-form_top_message { margin-bottom: 16px; }';
		}

		//Hide links.
		$registerLinkEnabled = ameUtils::get($this->settings, 'register_link_enabled', true);
		$lostPasswordLinkEnabled = ameUtils::get($this->settings, 'lost_password_link_enabled', true);
		$backToLinkEnabled = ameUtils::get($this->settings, 'back_to_link_enabled', true);

		//If both nav. links are hidden, hide the entire #nav element.
		//Different views have different nav links so we only do this for the "login" action.
		if ( !$registerLinkEnabled && !$lostPasswordLinkEnabled ) {
			$styles[] = 'body.login-action-login #nav {display: none;}';
		}

		//Hide the "lost your password?" link. This is complicated by the fact that the link
		//doesn't have an ID or a class.
		if ( !$lostPasswordLinkEnabled ) {
			$parsedUrl = parse_url(wp_lostpassword_url());
			$styles[] = sprintf(
				'#nav a[href*="%s"] {display: none;}',
				esc_url($parsedUrl['path'] . (empty($parsedUrl['query']) ? '' : ('?' . $parsedUrl['query'])))
			);
		}
		//TODO: In some views, this will leave the " | " link separator visible.

		//Hide the "back to site" link.
		if ( !$backToLinkEnabled ) {
			$styles[] = '#backtoblog {display: none;}';
		}

		//Add user CSS.
		$customCss = trim(strval(ameUtils::get($this->settings, 'custom_css', '')));
		if ( !empty($customCss) ) {
			$styles[] = $customCss;
		}

		if ( empty($styles) ) {
			return;
		}

		printf('<style id="ame-login-page-styles">%s</style>', implode("\n", $styles));
	}

	protected function escapeUrlForCss($url) {
		//This is not quite right, but it should work for most URLs.
		return esc_url_raw($url);
	}

	public function enqueueLoginPageScripts() {
		if ( !empty($this->settings['form_top_message']) || !empty($this->settings['form_bottom_message']) ) {
			wp_enqueue_script('jquery');
		}
	}

	public function printInternalFormMessages() {
		//There are no hooks for adding messages to the top/bottom of the form, so we'll output
		//them in hidden elements and then move them to the right place with JavaScript.
		$items = array(
			'form_top_message'    => 'prependTo',
			'form_bottom_message' => 'appendTo',
		);
		$script = array();

		foreach ($items as $key => $operation) {
			$message = trim(strval(ameUtils::get($this->settings, $key, '')));
			$htmlId = 'ame-login-' . $key;
			if ( !empty($message) ) {
				printf('<div id="%s" style="display: none">%s</div>', esc_attr($htmlId), $message);
				$script[] = sprintf('jQuery("#%s").%s("#loginform").show();', $htmlId, $operation);
			}
		}

		if ( !empty($script) ) {
			?>
			<script type="text/javascript">
				jQuery(document).ready(function () {
					<?php echo implode("\n", $script); ?>
				});
			</script>
			<?php
		}
	}

	public function printCustomJs() {
		$customJs = ameUtils::get($this->settings, 'custom_js', '');
		if ( empty($customJs) ) {
			return;
		}

		echo '<script type="text/javascript">';
		echo $customJs;
		echo '</script>';
	}

	public function getExportOptionLabel() {
		return 'Login page settings';
	}

	public function exportSettings() {
		$result = parent::exportSettings();
		//If the result is NULL or empty, there's nothing to do.
		if ( empty($result) ) {
			return $result;
		}

		//Importing/exporting attachments is complicated so let's not do that now.
		$skippedOptions = array(
			'logo_image.attachmentId',
			'page_background.image.attachmentId',
			//Used in older versions.
			'logo_image_attachment_id',
			'logo_image_attachment_details',
			'page_background_image_attachment_id',
			'page_background_image_attachment_details',
		);
		foreach ($skippedOptions as $option) {
			ameMultiDictionary::delete($result, $option);
		}

		return $result;
	}
}