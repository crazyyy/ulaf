<?php

class ameBrandingHideableHelper {
	private $module;
	private $hideableOptions;
	private $itemPrefix;
	private $createCategoryCallback;
	private $saveCallback;
	private $invertItems;
	private $defaultSetting;

	/**
	 * @param \amePersistentModule $module
	 * @param array $hideableOptions
	 * @param string $itemPrefix
	 * @param callable $createCategoryCallback
	 * @param callable $saveCallback
	 * @param int $registrationPriority
	 * @param boolean|null $invertItems
	 */
	public function __construct(
		$module,
		$hideableOptions,
		$itemPrefix,
		$createCategoryCallback,
		$saveCallback,
		$registrationPriority = 10,
		$invertItems = null,
		$defaultSetting = false
	) {
		$this->module = $module;
		$this->hideableOptions = $hideableOptions;
		$this->itemPrefix = $itemPrefix;
		$this->createCategoryCallback = $createCategoryCallback;
		$this->saveCallback = $saveCallback;
		$this->invertItems = $invertItems;
		$this->defaultSetting = $defaultSetting;

		add_action(
			'admin_menu_editor-register_hideable_items',
			array($this, 'registerHideableItems',),
			$registrationPriority,
			1
		);
		add_filter('admin_menu_editor-save_hideable_items', array($this, 'saveHideableItems'), 10, 2);
	}

	/**
	 * @param \YahnisElsts\AdminMenuEditor\EasyHide\HideableItemStore $store
	 * @return void
	 */
	public function registerHideableItems($store) {
		$settings = $this->module->loadSettings();

		$category = call_user_func($this->createCategoryCallback, $store);

		foreach ($this->hideableOptions as $option => $label) {
			$store->addBinaryItem(
				$this->itemPrefix . $option,
				$label,
				array($category),
				null,
				ameUtils::get($settings, $option, $this->defaultSetting),
				null,
				null,
				$this->invertItems
			);
		}
	}

	/**
	 * @param array $errors
	 * @param array $items
	 * @return array
	 */
	public function saveHideableItems($errors, $items) {
		$settings = $this->module->loadSettings();
		$anySettingsChanged = false;

		foreach ($this->hideableOptions as $option => $label) {
			$id = $this->itemPrefix . $option;
			if ( isset($items[$id]) ) {
				$enabled = ameUtils::get($items[$id], 'enabledForAll', false);
				$wasEnabled = ameUtils::get($settings, $option, $this->defaultSetting);

				if ( $enabled !== $wasEnabled ) {
					$settings[$option] = $enabled;
					$anySettingsChanged = true;
				}
			}
		}

		if ( $anySettingsChanged ) {
			call_user_func($this->saveCallback, $settings);
		}

		return $errors;
	}
}