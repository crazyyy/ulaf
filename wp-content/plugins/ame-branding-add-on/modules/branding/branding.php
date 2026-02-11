<?php

use YahnisElsts\AdminMenuEditor\Customizable\Controls\RadioGroup;
use YahnisElsts\AdminMenuEditor\Customizable\Controls\Section;
use YahnisElsts\AdminMenuEditor\Customizable\SettingsForm;
use YahnisElsts\AdminMenuEditor\Customizable\Storage\ModuleSettings;

class ameBrandingEditor extends amePersistentProModule {
	const GREETING_FILTER_PRIORITY = 10;

	protected $optionName = 'ws_ame_general_branding';

	protected $tabTitle = 'Branding';
	protected $tabSlug = 'branding';

	protected $settingsFormAction = 'ame_save_branding_settings';
	/**
	 * @var null|\YahnisElsts\AdminMenuEditor\Customizable\SettingsForm
	 */
	protected $form = null;

	protected static $hideableOptions = array(
		'is_toolbar_wp_logo_hidden'   => 'WordPress logo in the Toolbar',
		'is_admin_footer_hidden'      => 'Admin footer',
		'is_footer_version_hidden'    => 'WordPress version in the admin footer',
		'is_right_now_version_hidden' => 'WordPress version in the "At a Glance" widget',
	);

	public function __construct($menuEditor) {
		$this->settingsWrapperEnabled = true;
		parent::__construct($menuEditor);

		add_action('admin_init', array($this, 'registerAdminHooks'));

		add_action('admin_bar_menu', array($this, 'customizeWordPressToolbar'), 15, 1);
		add_action('admin_print_styles', array($this, 'adjustToolbarLogoStyles'));
		add_action('add_admin_bar_menus', array($this, 'registerGreetingHooks'));

		//Change the "From" name and address used by wp_mail(). Other plugins (e.g. contact forms)
		//also need to be able to change this, so let's use priority 9 instead of the default 10.
		add_filter('wp_mail_from_name', array($this, 'filterFromName'), 9, 1);
		add_filter('wp_mail_from', array($this, 'filterFromEmail'), 9, 1);

		add_action('admin_menu_editor-register_ac_items', array($this, 'registerAdminCustomizerItems'), 20);
	}

	public function enqueueTabScripts() {
		parent::enqueueTabScripts();

		wp_enqueue_media();
		wp_enqueue_script('ame-branding-image-selector');

		wp_enqueue_auto_versioned_script(
			'ws-ame-general-branding-settings',
			plugins_url('modules/branding/branding.js', AME_BRANDING_ADD_ON_FILE),
			array('jquery', 'ame-branding-image-selector')
		);
	}

	public function enqueueTabStyles() {
		parent::enqueueTabStyles();

		wp_enqueue_auto_versioned_style(
			'ame-branding-tab-styles',
			plugins_url('modules/branding/branding.css', AME_BRANDING_ADD_ON_FILE)
		);
	}

	public function handleSettingsForm($post = array()) {
		$this->getSettingsForm()->handleUpdateRequest($post);
	}

	public function createSettingInstances(ModuleSettings $settings) {
		$f = $settings->settingFactory();

		return array(
			$f->boolean(
				'is_toolbar_wp_logo_hidden',
				'Remove the WordPress logo from the Toolbar',
				['groupTitle' => 'WordPress logo']
			),
			$f->image('custom_toolbar_logo', 'Custom logo'),
			$f->url('custom_toolbar_logo_link', 'Logo link URL'),
			$f->plainText(
				'custom_howdy_text',
				'Custom "Howdy" text',
				[
					'customValidators' => [
						function ($value, WP_Error $errors) {
							//WordPress will pass the value to sprintf(), so we need to check
							//for invalid format strings.

							//Check for invalid placeholders. Only "%s" is allowed.
							//For example, there was a case where someone accidentally left out the "s".
							if ( preg_match('/%(?:$|[^s])/', $value, $matches) ) {
								$errors->add(
									'invalid_format',
									'The greeting text can only contain one "%s" placeholder.'
									. ' "' . $matches[0] . '" is not recognized.'
								);
								return $errors;
							}

							//Check for multiple "%s" placeholders. There is only one argument:
							//the user's display name.
							if ( substr_count($value, '%s') > 1 ) {
								$errors->add(
									'invalid_format',
									'The greeting text can only contain one "%s" placeholder.'
									. ' Multiple "%s" placeholders were found.'
								);
								return $errors;
							}

							return $value;
						},
					],
				]
			),

			$f->boolean(
				'is_admin_footer_hidden',
				'Hide the entire admin footer',
				['default' => false, 'groupTitle' => 'Visibility']
			),
			$f->boolean(
				'is_footer_version_hidden',
				'Remove WordPress version information from the admin footer',
				['default' => false, 'groupTitle' => 'WordPress version']
			),
			$f->userHtml('admin_footer_text', 'Footer text', ['trimmed' => true]),

			$f->plainText('admin_page_title_template', 'Admin page titles'),
			$f->enum(
				'core_update_notification_visibility',
				array('default', 'update_core', 'hidden'),
				'WordPress update notifications',
				['default' => 'default']
			),
			$f->boolean(
				'is_right_now_version_hidden',
				'Remove the WordPress version from the "At a Glance" widget',
				['default' => false, 'groupTitle' => 'WordPress version in Dashboard']
			),

			$f->plainText('wp_mail_from_name', 'Sender name'),
			$f->plainText('wp_mail_from_email', '"From" email'),
		);

		/*
	     * Feature idea: Text Replacement
	     *  Replace "WordPress" in HTML with something else
	     *  Probably using output buffers or gettext filters. That means you can only replace translated/-able text.
	     */
	}

	protected function getSettingAliases() {
		return array(
			'custom_toolbar_logo.attachmentId'  => 'custom_toolbar_logo_attachment_id',
			'custom_toolbar_logo.externalUrl'   => 'custom_toolbar_logo_external_url',
			'custom_toolbar_logo.attachmentUrl' => 'custom_toolbar_logo_attachment_url',
		);
	}

	protected function getInterfaceStructure() {
		$settings = $this->loadSettings();
		$b = $settings->elementBuilder();

		$structure = $b->structure(
			$b->section(
				'Toolbar',
				$b->auto('is_toolbar_wp_logo_hidden'),
				$b->auto('custom_toolbar_logo')
					->description('Recommended size: 16x16 px.'),
				$b->auto('custom_toolbar_logo_link'),
				$b->auto('custom_howdy_text')
					->description(
						'Enter the greeting to use instead of "howdy". Example: <code>Welcome</code>. '
						. ' Alternatively, you can enter a sentence like <code>Hi, %s!</code>. '
						. ' The <code>%s</code> will be replaced with the user\'s display name.'
					)
			)->id('ame-branding-toolbar'),
			$b->section(
				'Admin Footer',
				$b->auto('is_admin_footer_hidden'),
				$b->editor('admin_footer_text'),
				$b->auto('is_footer_version_hidden')
			)->id('ame-branding-admin-footer'),
			$b->section(
				'Admin Settings',
				$b->group(
					'Admin page titles',
					$b->textBox('admin_page_title_template')
						->code()
						->inputClasses('large-text'),

					//These tag buttons are handled by JS. This could be a custom
					//control, but static HTML works fine in this case
					$b->html('<p>Available tags:</p>'),
					$b->html('<ul class="ame-page-title-tags">
						<li><button class="button button-secondary" type="button" title="Current admin page or menu item">%page%</button></li>
						<li><button class="button button-secondary" type="button" 
							title="The &quot;Site Title&quot; set in &quot;Settings -&gt; General&quot;">%site_title%</button></li>
					</ul>'),
					$b->html('<p>Examples:</p><ul class="ame-page-title-examples">
						<li><code>' . '%page% &lsaquo; %site_title% &#8212; Company Name' . '</code></li>
						<li><code>' . htmlspecialchars('Company Name &rsaquo; %page%') . '</code></li>
						<li><code>' . htmlspecialchars('%site_title% - %page%') . '</code></li>
					</ul>')
				),
				$b->radioGroup('core_update_notification_visibility')
					->params([
						'choices' => function () {
							$choices = [];
							//In Multisite, only users who have the "update_core" capability
							//can see the core update notification, so the "update_core" and
							//"default" options are equivalent.
							$canUpdateLabel = 'Show to users who can install updates';
							if ( is_multisite() ) {
								$choices['default'] = $canUpdateLabel;
							} else {
								$choices['default'] = 'Show to all users';
								$choices['update_core'] = $canUpdateLabel;
							}
							$choices['hidden'] = 'Hide from all users';
							return $choices;
						},
						'wrap'    => RadioGroup::WRAP_LINE_BREAK,
					]),
				$b->auto('is_right_now_version_hidden')
			),
			$b->section(
				'WordPress Emails',
				$b->auto('wp_mail_from_name')
					->description('The default is <code>WordPress</code>.'),
				$b->textBox('wp_mail_from_email')
					->type('email')
					->description(function () {
						//This is how the wp_mail() function chooses the default "From" address in WP 4.9.4.
						$hostname = strtolower($_SERVER['SERVER_NAME']);
						if ( substr($hostname, 0, 4) === 'www.' ) {
							$hostname = substr($hostname, 4);
						}
						$defaultFromEmail = 'wordpress@' . $hostname;
						return sprintf(
							'The default is <code>%s</code>.',
							esc_html($defaultFromEmail)
						);
					})
			)->description(
				'You can change the "From" header for all emails sent by WordPress. 
				These settings will also affect any plugins that don\'t specify their own "From" header.'
			)
		);

		return $structure->build();
	}

	protected function getSettingsForm() {
		if ( $this->form === null ) {
			//In Multisite, "update_core" is equivalent to "default", so the "update_core" option
			//is not shown. However, if the user imports settings from a non-Multisite site,
			//the "update_core" value will be present in the DB. This will cause the settings
			//page to show an error when saving because none of the options will be selected
			//("default" or "hidden"). To avoid this, let's replace "update_core" with "default".
			$settings = $this->loadSettings();
			if ( is_multisite() && ($this->getOption('core_update_notification_visibility') === 'update_core') ) {
				$settings->set('core_update_notification_visibility', 'default');
			}

			$this->form = SettingsForm::builder('ame_save_branding_settings')
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

	/**
	 * @param \YahnisElsts\AdminMenuEditor\AdminCustomizer\AmeAdminCustomizer $adminCustomizer
	 * @return void
	 */
	public function registerAdminCustomizerItems($adminCustomizer) {
		$settings = $this->loadSettings();
		$adminCustomizer->addSettings($settings->getRegisteredSettings());

		$structure = $this->getInterfaceStructure();

		$toolbarBrandingSection = $structure->findChildById('ame-branding-toolbar');
		$existingToolbarSection = $adminCustomizer->findSection('ame-ds-toolbar');
		if ( $existingToolbarSection->isDefined() && ($toolbarBrandingSection instanceof Section) ) {
			//Change the section title to "Branding" and make it a subsection of the "Toolbar" section.
			$toolbarBrandingSection->setTitle('Branding');
			$existingToolbarSection->get()->add($toolbarBrandingSection);
		} else {
			$adminCustomizer->addSection($toolbarBrandingSection);
		}

		$footerSection = $structure->findChildById('ame-branding-admin-footer');
		if ( $footerSection instanceof Section ) {
			//Use a shorter title in the admin customizer. The "Admin" part is already implied.
			$footerSection->setTitle('Footer');
		}
		$adminCustomizer->addSection($footerSection);
	}

	private function getOption($name, $default = null) {
		if ( $this->settings === null ) {
			$this->loadSettings();
		}

		return ameUtils::get($this->settings, $name, $default);
	}

	/**
	 * Optimization: Only register admin-related hooks in the admin area, not on every page load.
	 */
	public function registerAdminHooks() {
		//Register hideable items as needed.
		require_once(AME_BRANDING_ADD_ON_DIR . '/includes/ameBrandingHideableHelper.php');

		$me = $this;
		new ameBrandingHideableHelper(
			$this,
			self::$hideableOptions,
			'br/',
			/**
			 * @param \YahnisElsts\AdminMenuEditor\EasyHide\HideableItemStore $store
			 * @return \YahnisElsts\AdminMenuEditor\EasyHide\Category
			 */
			function ($store) {
				return $store->getOrCreateCategory('admin-ui', 'General', null, true);
			},
			function ($settings) use ($me) {
				$me->settings = $settings;
				$me->saveSettings();
			},
			20,
			false //Not inverted, unlike many other items.
		);

		$this->loadSettings();
		if ( empty($this->settings) ) {
			return; //Nothing to do.
		}

		//Customize the admin title.
		if ( !empty($this->settings['admin_page_title_template']) ) {
			add_filter('admin_title', array($this, 'filterAdminTitle'), 10, 2);
		}

		if ( !empty($this->settings['is_admin_footer_hidden']) ) {
			add_action('admin_print_styles', array($this, 'hideAdminFooter'));
		}

		//Replace the footer text. Alternatively, we could use the "in_admin_footer" action.
		if ( !empty($this->settings['admin_footer_text']) ) {
			add_filter('admin_footer_text', array($this, 'filterFooterText'), 200, 0);
		}

		//Remove the WordPress version from the footer.
		if ( !empty($this->settings['is_footer_version_hidden']) ) {
			add_filter('update_footer', '__return_empty_string', 990);
		}

		//Hide core update notifications.
		$visibility = $this->getOption('core_update_notification_visibility', 'default');
		if ( ($visibility === 'hidden') || (($visibility === 'update_core') && !current_user_can('update_core')) ) {
			remove_action('admin_notices', 'update_nag', 3);
		}

		//Remove the WordPress version from the "At a Glance" widget (previously, it was called "Right Now").
		if ( !empty($this->settings['is_right_now_version_hidden']) ) {
			add_action('admin_print_styles-index.php', array($this, 'hideRightNowVersion'));
		}
	}

	/**
	 * @param WP_Admin_Bar|null $wpAdminBar
	 */
	public function customizeWordPressToolbar($wpAdminBar = null) {
		if ( !$wpAdminBar ) {
			return;
		}

		$settings = $this->loadSettings();
		if ( ameUtils::get($settings, 'is_toolbar_wp_logo_hidden', false) ) {
			//Remove all logo submenus. Most of them are in the "wp-logo-external" group,
			//but that group won't be registered yet when this function is called.
			$parents = array('wp-logo', 'wp-logo-external');
			$nodes = $wpAdminBar->get_nodes();
			foreach ($nodes as $node) {
				if ( !empty($node->parent) && in_array($node->parent, $parents, true) ) {
					$wpAdminBar->remove_node($node->id);
				}
			}
			//Remove the logo itself.
			$wpAdminBar->remove_node('wp-logo');
		}

		//Unfortunately, ameUtils::get() doesn't use read aliases for nested paths,
		//so we have these two branches for backwards compatibility.
		if ( !is_array($settings) && ($settings instanceof ModuleSettings) ) {
			$logoUrl = $settings->get('custom_toolbar_logo.externalUrl');
			if ( empty($logoUrl) ) {
				$logoUrl = $settings->get('custom_toolbar_logo.attachmentUrl');
			}
			$logoAttachmentId = $settings->get('custom_toolbar_logo.attachmentId');
		} else {
			$logoUrl = ameUtils::get($settings, 'custom_toolbar_logo.externalUrl');
			if ( empty($logoUrl) ) {
				$logoUrl = ameUtils::get($settings, 'custom_toolbar_logo.attachmentUrl');
			}
			$logoAttachmentId = ameUtils::get($settings, 'custom_toolbar_logo.attachmentId');
		}
		//If the attachment URL isn't cached, but we have an attachment ID,
		//try to fetch the attachment URL the hard way.
		if (
			empty($logoUrl)
			&& ($logoAttachmentId !== null)
			&& ($settings instanceof ModuleSettings)
		) {
			/** @var \YahnisElsts\AdminMenuEditor\Customizable\Settings\ImageSetting $toolbarLogoSetting */
			$toolbarLogoSetting = $settings->getSetting('custom_toolbar_logo');
			$logoUrl = $toolbarLogoSetting->getImageUrl();
		}
		if ( empty($logoUrl) ) {
			return;
		}

		//Simply adding a new item wouldn't work because WordPress automatically hides
		//custom items when the viewport is too narrow (responsive layout). We have to
		//replace the default logo node.
		$escapedUrl = esc_attr($logoUrl);
		$image = '<img src="' . $escapedUrl . '" style="display: inline-block; max-height: 100%;'
			. 'padding: 0; margin: 0; vertical-align: top; position: relative;'
			. 'top: 50%; -ms-transform: translateY(-50%); transform: translateY(-50%);">';

		$wpAdminBar->add_node(array(
			'id'    => 'wp-logo',
			'title' => $image,
			'href'  => ameUtils::get($settings, 'custom_toolbar_logo_link'),
			'meta'  => array(
				'title' => '',
				'class' => '',
			),
		));
	}

	public function adjustToolbarLogoStyles() {
		echo '<style>
		@media screen and ( max-width: 782px ) {
			#wpadminbar #wp-admin-bar-wp-logo a,
			 #wpadminbar #wp-admin-bar-wp-logo .ab-empty-item
			 {width: 52px; text-align: center;}
		}
		</style>';
	}

	/**
	 * Apply custom admin titles.
	 *
	 * @param string $adminTitle The full page title.
	 * @param string $title The original title from the current menu item.
	 * @return string
	 */
	public function filterAdminTitle(/** @noinspection PhpUnusedParameterInspection */
		$adminTitle = '', $title = ''
	) {
		//The template should already be sanitized, but let's do a bit of that again just to be sure.
		$template = strip_tags($this->settings['admin_page_title_template']);

		return str_replace(
			array('%page%', '%site_title%'),
			array($title, get_bloginfo('name')),
			$template
		);
	}

	/**
	 * Set up the hooks that change the "Howdy, username" greeting in the Toolbar.
	 *
	 * We use the "gettext" filter to replace "Howdy" with a custom greeting.
	 * However, we don't want to keep the hook active for long because filtering
	 * every localized string could hurt performance. Every admin page triggers
	 * this filter hundreds of times.
	 *
	 * To avoid the performance hit, let's add the filter callback just before
	 * WordPress creates the "Howdy..." node and then remove it afterward.
	 */
	public function registerGreetingHooks() {
		//Optimization: Do nothing if there's no custom greeting.
		$customGreeting = $this->getOption('custom_howdy_text', '');
		if ( empty($customGreeting) ) {
			return;
		}

		//In WP 4.9.x the "howdy" node is added by the wp_admin_bar_my_account_item function.
		$accountMenuHookPriority = has_action('admin_bar_menu', 'wp_admin_bar_my_account_item');
		if ( $accountMenuHookPriority !== false ) {
			$addPriority = max($accountMenuHookPriority - 1, 0);
			$removePriority = $accountMenuHookPriority + 1;
		} else {
			//Fallback.
			$addPriority = 0;
			$removePriority = 90;
		}

		add_action('admin_bar_menu', array($this, 'addGreetingFilter'), $addPriority);
		add_action('admin_bar_menu', array($this, 'removeGreetingFilter'), $removePriority);
	}

	public function addGreetingFilter() {
		add_filter('gettext', array($this, 'changeGreeting'), self::GREETING_FILTER_PRIORITY, 3);
	}

	public function removeGreetingFilter() {
		remove_filter('gettext', array($this, 'changeGreeting'), self::GREETING_FILTER_PRIORITY);
	}

	/**
	 * @param string $translation
	 * @param string $text
	 * @param string $domain
	 * @return string
	 */
	public function changeGreeting($translation, $text = '', $domain = 'default') {
		if ( ($text === 'Howdy, %s') && ($domain === 'default') ) {
			$customGreeting = $this->getOption('custom_howdy_text', '');
			if ( strpos($customGreeting, '%s') === false ) {
				$translation = str_replace('Howdy', $customGreeting, $text);
			} else {
				$translation = $customGreeting;
			}
		}
		return $translation;
	}

	public function hideAdminFooter() {
		echo '<style>#wpfooter {display: none !important;}</style>';
	}

	public function filterFooterText() {
		return do_shortcode($this->settings['admin_footer_text']);
	}

	public function hideRightNowVersion() {
		echo '<style>#wp-version-message {display: none;}</style>';
	}

	public function filterFromName($name) {
		$customName = $this->getOption('wp_mail_from_name', '');
		if ( !empty($customName) ) {
			return $customName;
		}
		return $name;
	}

	public function filterFromEmail($email) {
		$customEmail = $this->getOption('wp_mail_from_email', '');
		if ( !empty($customEmail) ) {
			return $customEmail;
		}
		return $email;
	}

	public function exportSettings() {
		$result = parent::exportSettings();

		//Importing/exporting attachments is complicated so let's not do that now.
		$skippedSettings = array('custom_toolbar_logo_attachment_id', 'custom_toolbar_logo_attachment_url');
		foreach ($skippedSettings as $key) {
			unset($result[$key]);
		}
		return $result;
	}

	public function getExportOptionLabel() {
		return 'Branding settings';
	}
}