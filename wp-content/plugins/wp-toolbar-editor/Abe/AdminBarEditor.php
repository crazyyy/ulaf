<?php

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;
use YahnisElsts\PluginUpdateChecker\v5p4\Plugin\Update;

class Abe_AdminBarEditor {
	const PLUGIN_NAME = 'WordPress Toolbar Editor';
	const PLUGIN_MENU_TITLE = 'Toolbar Editor';

	const ADMIN_BAR_FILTER_PRIORITY = 50000;
	const MAX_IMPORT_FILE_SIZE = 5242880; //5 MB

	const WPML_CONTEXT = 'wp-toolbar-editor toolbar texts';

	/** @const string Per-site option that specifies whether to use a site-specific config instead of the global one. */
	const MENU_SCOPE_OVERRIDE_OPTION = 'ws_abe_override_global_menu';

	const HIDEABLE_COMPONENT_ID = 'tbe';
	const HIDEABLE_ITEM_PREFIX = 'tbe/';

	const TIMESTAMP_UPDATE_INTERVAL = 4 * 24 * 60 * 60;
	const TIMESTAMP_UPDATE_JITTER = 6 * 60 * 60;
	const MIN_TIMESTAMP_UPDATE_INTERVAL = 2 * 24 * 60 * 60;

	const STALENESS_THRESHOLD_IN_DAYS = 14;

	/** @var string Database option that will be used to store plugin settings. */
	protected $optionName = 'ws_abe_admin_bar_settings';
	/** @var array|null */
	protected $settings = null;

	/** @var string Database option that will store the custom admin bar menu. */
	protected $menuOptionName = 'ws_abe_admin_bar_nodes';

	/** @var string Admin page slug. */
	protected $pageSlug = 'ws-admin-bar-editor';

	/** @var StdClass[] Default admin bar configuration. Each node is a plain object (WP format). */
	protected $defaultNodes = [];

	/** @var Abe_Node[] Current admin bar configuration with custom settings and defaults merged. */
	protected $mergedNodes = [];

	/** @var array Query arguments. */
	protected $get;
	/** @var array POST fields. */
	protected $post;
	protected $originalPost;

	protected $updateChecker = null;

	/** @var Wslm_LicenseManagerClient */
	protected $ameLicenseManager = null;

	public function __construct() {
		if ( is_admin() ) {
			$this->loadSettings();
		}

		//Capture request arguments before WP has had a chance to apply magic quotes.
		$this->get = $_GET;
		$this->post = $this->originalPost = $_POST;
		if (
			version_compare(phpversion(), '7.4.0alpha1', '<')
			&& function_exists('get_magic_quotes_gpc')
			&& @get_magic_quotes_gpc()
		) {
			$this->post = stripslashes_deep($this->post);
			$this->get = stripslashes_deep($this->get);
		}

		add_action('wp_before_admin_bar_render', [$this, 'filterAdminBar'], self::ADMIN_BAR_FILTER_PRIORITY);

		if ( defined('WS_AME_INTERNAL_VERSION') && (WS_AME_INTERNAL_VERSION >= 2024.001) ) {
			add_action('admin_menu_editor-editor_menu_registered', [$this, 'addEditorPage'], 12);
		} else {
			add_action('admin_menu', [$this, 'addEditorPage']);
		}

		add_action('admin_menu_editor-register_hideable_items', [$this, 'registerHideableItems'], 10, 1);
		add_filter(
			'admin_menu_editor-save_hideable_items-' . self::HIDEABLE_COMPONENT_ID,
			[$this, 'saveHideableItems'], 10, 2
		);

		if ( !defined('IS_DEMO_MODE') && !defined('IS_MASTER_MODE') ) {
			//Add-ons are updated separately from the main plugin, but use the same license details.
			require_once WS_ADMIN_BAR_EDITOR_DIR . '/vendor/autoload.php';
			$this->updateChecker = PucFactory::buildUpdateChecker(
				'https://adminmenueditor.com/?get_metadata_for=wp-toolbar-editor',
				WS_ADMIN_BAR_EDITOR_FILE,
				'wp-toolbar-editor',
				12,
				'ws_abe_external_updates' //Set the option name explicitly so that we can delete it when uninstalling.
			);

			if ( isset($GLOBALS['ameProLicenseManager']) ) {
				$this->ameLicenseManager = $GLOBALS['ameProLicenseManager'];

				$this->updateChecker->addQueryArgFilter([$this, 'filterUpdateChecks']);

				$downloadFilter = [$this, 'filterUpdateDownloadUrl'];
				$this->updateChecker->addFilter('request_info_result', $downloadFilter, 20);
				$this->updateChecker->addFilter('pre_inject_update', $downloadFilter);
				$this->updateChecker->addFilter('pre_inject_info', $downloadFilter);
			}
		}
	}

	public function filterAdminBar() {
		//Get the admin bar instance.
		global $wp_admin_bar;
		/** @var WP_Admin_Bar $wp_admin_bar */
		if ( !isset($wp_admin_bar) ) {
			return; //This should never happen, but let's not crash if it does.
		}
		$adminBar = $wp_admin_bar;

		$visibleDefaultNodes = $adminBar->get_nodes();
		//Some buggy themes (e.g. Avada 3.6.2) trigger admin bar rendering *twice*. The list of nodes
		//will be null the second time around. That's fine - we already processed it once.
		if ( $visibleDefaultNodes === null ) {
			return;
		}

		$this->defaultNodes = $visibleDefaultNodes;
		$customNodes = $this->loadCustomMenu();

		//Some admin bar items - like "Edit Post" - are only created in specific contexts, and they
		//won't exist when the user opens the editor. We still want the user to be able to edit those
		//items, though, so we'll need to register them manually.
		//(Only do it on the editor page for performance.)
		if ( $this->isEditorPage() ) {
			$this->defaultNodes = $this->addAllContextualNodes($this->defaultNodes);
		}

		//Sort the existing items in depth-first iteration order to match the order used by the editor.
		$this->defaultNodes = $this->sortNodesDepthFirst($this->defaultNodes);

		//Get visibility settings for new toolbar items from the menu editor.
		//The default is to show new items to everyone, but the user can choose to auto-hide new items.
		$newNodeVisibility = [];
		$menuEditor = $GLOBALS['wp_menu_editor'];
		if ( isset($menuEditor) && method_exists($menuEditor, 'get_new_menu_grant_access') ) {
			/** @var WPMenuEditor $menuEditor */
			$newNodeVisibility = $menuEditor->get_new_menu_grant_access();
		}

		$foundNewNodesToSave = false;
		$needTimestampUpdate = false;

		//For better performance, let's update the timestamp only every X days instead of
		//every page load. Also, let's randomize the update interval a bit to reduce the chances
		//of multiple requests updating the same nodes at the same time.
		$elapsedTimeThreshold = min(
			self::TIMESTAMP_UPDATE_INTERVAL - wp_rand(0, self::TIMESTAMP_UPDATE_JITTER),
			self::MIN_TIMESTAMP_UPDATE_INTERVAL
		);
		$now = time();

		//Merge existing admin bar items with our custom configuration.
		$this->mergedNodes = $customNodes;
		$previousNodeId = null;
		foreach ($this->defaultNodes as $wpNode) {
			if ( isset($this->mergedNodes[$wpNode->id]) ) {
				$node = $this->mergedNodes[$wpNode->id];

				//We always update the in-memory timestamp, but only save that change to the DB
				//if the old timestamp is stale enough. This is to avoid unnecessary DB writes.
				$previousTimestamp = $node->last_seen_timestamp;
				$node->last_seen_timestamp = $now;
				$needTimestampUpdate = $needTimestampUpdate
					|| empty($previousTimestamp)
					|| (($now - $previousTimestamp) > $elapsedTimeThreshold);

				/*if ($needTimestampUpdate) {
					error_log(sprintf(
						'Node %s: Updating timestamp. Previous: %s, new: %s',
						$node->id,
						$previousTimestamp,
						$node->last_seen_timestamp
					));
				}*/

				$node->setDefaultsFromNodeArgs($wpNode);
			} else {
				/*error_log(sprintf(
					'WS Admin Bar Editor: Found new node %s',
					$wpNode->id
				));*/

				$node = Abe_Node::fromNodeArgs($wpNode);
				$node->last_seen_timestamp = $now;
				if ( !empty($newNodeVisibility) ) {
					$node->is_visible_to_actor = $newNodeVisibility;
				}

				//Some nodes like the dynamic site links in Multisite will not be saved
				//even if they are detected.
				if ( $this->canIncludeInConfiguration($node) ) {
					$foundNewNodesToSave = true;
				}

				if ( $previousNodeId === null ) {
					//Insert it at the beginning.
					//Note: Consider adding the node after its parent if it has one.
					$this->mergedNodes = [$node->id => $node] + $this->mergedNodes;
				} else {
					//Insert it after the previous node.
					$this->mergedNodes = $this->insertAfter(
						$this->mergedNodes,
						$previousNodeId,
						[$node->id => $node]
					);
				}
			}
			$previousNodeId = $node->id;
		}

		if (
			$this->userCanAccessPlugin()
			&& ($foundNewNodesToSave || $needTimestampUpdate)
			&& $this->isNodeDetectionEnabled()
			&& !empty($customNodes) //Juuust in case, let's not do it when there is no custom config.
		) {
			/*if ($foundNewNodes) {
				error_log('WS Admin Bar Editor: Found new nodes.');
			}
			if ($needTimestampUpdate) {
				error_log('WS Admin Bar Editor: Updating timestamps.');
			}*/

			//Save new nodes and updated timestamps to the database.
			add_action('shutdown', [$this, 'saveCurrentMergedNodes']);
		}

		//Get the current user's roles to determine which items they can see.
		$currentActor = [];
		$user = wp_get_current_user();
		if ( isset($user, $user->roles) && is_array($user->roles) ) {
			foreach ($user->roles as $role) {
				$currentActor[] = 'role:' . $role;
			}
		}
		if ( is_multisite() && is_super_admin() ) {
			$currentActor[] = 'special:super_admin';
		}
		$loginActor = null;
		if ( isset($user, $user->user_login) ) {
			$loginActor = 'user:' . $user->user_login;
		}

		//Apply the custom configuration.
		foreach ($this->mergedNodes as $node) {
			if ( !$node->isVisibleTo($currentActor, $loginActor) ) {
				$adminBar->remove_node($node->id);
			} else if ( $node->is_custom || isset($visibleDefaultNodes[$node->id]) ) {
				$adminBar->add_node($node->toNodeArgs());
			}
		}

		//Sort admin bar in the user-specified order.
		$this->setAdminBarOrder($adminBar, $this->mergedNodes);
	}

	/**
	 * Load the custom admin bar menu for the current site.
	 *
	 * @return Abe_Node[] List of admin bar nodes.
	 */
	protected function loadCustomMenu() {
		$this->loadSettings();

		if ( $this->shouldUseSiteSpecificMenu() ) {
			$nodes = get_option($this->menuOptionName, []);
		} else {
			$nodes = isset($this->settings['nodes']) ? $this->settings['nodes'] : [];
		}
		$nodes = array_map(['Abe_Node', 'fromArray'], $nodes);
		return $nodes;
	}

	/**
	 * Set the custom admin bar menu.
	 *
	 * This method will update either the global menu or the per-site menu option
	 * depending on how the plugin is configured for the current site.
	 *
	 * @param array|Abe_Node[] $nodes
	 */
	protected function saveCustomMenu($nodes) {
		if ( !empty($nodes) && (reset($nodes) instanceof Abe_Node) ) {
			foreach ($nodes as $index => $node) {
				$nodes[$index] = $node->toArray();
			}
		}

		$nodes = array_filter($nodes, [$this, 'canIncludeInConfiguration']);

		$isWpmlActive = function_exists('icl_register_string');
		$oldCustomMenu = $isWpmlActive ? $this->loadCustomMenu() : [];

		if ( $this->shouldUseSiteSpecificMenu() ) {
			update_option($this->menuOptionName, $nodes);
		} else {
			$this->loadSettings();
			$this->settings['nodes'] = $nodes;
			$this->saveSettings();
		}

		if ( $isWpmlActive ) {
			$newCustomMenu = $this->loadCustomMenu();
			$this->updateWpmlStrings($oldCustomMenu, $newCustomMenu);
		}
	}

	/**
	 * Determine if we should use a site-specific admin bar menu configuration
	 * for the current site, or fall back to the global config.
	 *
	 * @return bool True = use the site-specific config (if any), false = use the global config.
	 */
	protected function shouldUseSiteSpecificMenu() {
		//If this is a single-site WP installation then there's really
		//no difference between "site-specific" and "global".
		if ( !is_multisite() ) {
			return false;
		}

		$this->loadSettings();
		return ($this->settings['menu_config_scope'] === 'site') || get_option(self::MENU_SCOPE_OVERRIDE_OPTION, false);
	}

	/**
	 * Load plugin settings.
	 *
	 * Note that plugin settings are conceptually distinct from the actual
	 * admin bar configuration. Use loadCustomMenu() to load that instead.
	 *
	 * @param bool $forceReload
	 */
	protected function loadSettings($forceReload = false) {
		if ( isset($this->settings) && !$forceReload ) {
			return;
		}

		$settings = get_site_option($this->optionName, []);
		if ( !is_array($settings) ) {
			$settings = [];
		}
		$defaults = [
			'menu_config_scope'      => 'global', //'global' or 'site'
			'nodes'                  => [],
			'plugin_access'          => 'super_admin',
			'allowed_user_id'        => null,
			'node_detection_enabled' => true,
		];
		$this->settings = array_merge($defaults, $settings);
	}

	protected function saveSettings() {
		update_site_option($this->optionName, $this->settings);
	}

	/**
	 * @param array $nodes Array of WP Toolbar node objects indexed by node ID.
	 * @return array Sorted array of nodes in the same format.
	 */
	protected function sortNodesDepthFirst($nodes) {
		$tempRootId = '>TEroot20191204';
		$children = [$tempRootId => []];
		foreach ($nodes as $tempNode) {
			if ( !isset($tempNode->parent) || ($tempNode->parent === false) || ($tempNode->parent === '') ) {
				$parent = $tempRootId;
			} else {
				$parent = $tempNode->parent;
			}
			if ( !isset($children[$parent]) ) {
				$children[$parent] = [];
			}
			$children[$parent][] = $tempNode;
		}

		$sortedDefaultNodes = [];
		$this->traverseDefaultNodeTree($children, $tempRootId, $sortedDefaultNodes);
		return $sortedDefaultNodes;
	}

	/**
	 * @param array $children
	 * @param string $id
	 * @param array $output
	 */
	private function traverseDefaultNodeTree($children, $id, &$output) {
		if ( empty($children[$id]) ) {
			return;
		}
		foreach ($children[$id] as $node) {
			$output[$node->id] = $node;
			$this->traverseDefaultNodeTree($children, $node->id, $output);
		}
	}

	/**
	 * Sort admin bar nodes according to a list of IDs.
	 *
	 * This method will re-arrange the admin bar to match the key order of the $order array.
	 * Any nodes that don't have a matching key will be moved to the end of the admin bar.
	 *
	 * @param WP_Admin_Bar $adminBar
	 * @param array $order An array indexed by node ID.
	 */
	protected function setAdminBarOrder($adminBar, $order) {
		//Unfortunately, WP_Admin_Bar has no "sort" or "move_node" method, and it is not possible
		//to add one because the $nodes array is private. So we'll have to do this the hard way.
		$nodes = $adminBar->get_nodes();
		if ( empty($nodes) ) {
			return; //Nothing to do.
		}

		//1. Remove all nodes.
		foreach ($nodes as $wpNode) {
			$adminBar->remove_node($wpNode->id);
		}

		//2. Add them back in the right order.
		foreach ($order as $id => $unusedValue) {
			if ( isset($nodes[$id]) ) { //Hidden nodes have been removed by this point.
				$wpNode = $nodes[$id];
				$adminBar->add_node($wpNode);
				unset($nodes[$id]);
			}
		}

		//3. Add back any left-over nodes (theoretically, this should never happen).
		if ( !empty($nodes) ) {
			foreach ($nodes as $wpNode) {
				$adminBar->add_node($wpNode);
			}
		}
	}

	public function addEditorPage() {
		if ( $this->userCanAccessPlugin() ) {
			$page = add_options_page(
				self::PLUGIN_NAME,
				self::PLUGIN_MENU_TITLE,
				'manage_options', //Should we use a different cap if access is restricted to a specific user?
				$this->pageSlug,
				[$this, 'doAdminPage']
			);

			add_action('admin_print_scripts-' . $page, [$this, 'enqueueScripts']);
			add_action('admin_print_styles-' . $page, [$this, 'enqueueStyles']);

			if ( is_callable([ameMenuItem::class, 'add_class_to_submenu_item']) ) {
				ameMenuItem::add_class_to_submenu_item(
					'options-general.php',
					$this->pageSlug,
					'ws-ame-secondary-am-item'
				);
			}
		}
	}

	/**
	 * Check if the current user can access this plugin.
	 *
	 * @return bool
	 */
	protected function userCanAccessPlugin() {
		$this->loadSettings();
		$access = $this->settings['plugin_access'];

		if ( $access === 'super_admin' ) {
			return is_super_admin();
		} else if ( $access === 'specific_user' ) {
			return get_current_user_id() == $this->settings['allowed_user_id'];
		} else {
			return current_user_can($access);
		}
	}

	public function doAdminPage() {
		if ( !$this->userCanAccessPlugin() ) {
			wp_die(sprintf(
				'You do not have sufficient permissions to edit the WordPress Toolbar. Required: <code>%s</code>.',
				htmlentities($this->settings['plugin_access'])
			));
		}

		//Dispatch form action.
		if ( isset($this->post['action']) ) {
			$action = strval($this->post['action']);
			check_admin_referer($action);

			if ( $action == 'save_menu' ) {
				$this->actionSaveMenu();
			} else if ( $action == 'export_menu' ) {
				$this->actionExportMenu();
			} else if ( $action == 'import_menu' ) {
				$this->actionImportMenu();
			} else if ( $action == 'save_settings' ) {
				$this->actionSaveSettings();
			}
		}

		$hideDemoNotice = isset($_COOKIE['abe_hide_demo_notice']) && !empty($_COOKIE['abe_hide_demo_notice']);
		if ( $this->isDemoMode() && !$hideDemoNotice ) {
			$this->displayDemoNotice();
		}

		$subSection = isset($this->get['sub_section']) ? $this->get['sub_section'] : null;
		if ( $subSection == 'settings' ) {
			$this->displaySettingsPage();
		} else {
			$this->displayEditorPage();
		}
	}

	public function displayEditorPage() {
		//These variables are used by the editor page template.
		$currentConfiguration = Abe_Node::serializeNodeListForJs($this->mergedNodes);
		$defaultConfiguration = Abe_Node::serializeNodeListForJs($this->defaultNodes);

		$imagesUrl = esc_attr(plugins_url('images/', WS_ADMIN_BAR_EDITOR_FILE));
		$pageSlug = $this->pageSlug;
		$settingsPageUrl = $this->getSettingsPageUrl();

		$actors = [];
		foreach (self::getRoleNames() as $roleId => $name) {
			$actors['role:' . $roleId] = $name;
		};
		asort($actors);

		if ( function_exists('is_multisite') && is_multisite() ) {
			$actors['special:super_admin'] = 'Super Admin';
		}

		//Always include the current user as an actor.
		$currentUser = wp_get_current_user();
		$loginsToInclude = [$currentUser->user_login];

		//Include the selected visible users from AME.
		$menuEditor = $GLOBALS['wp_menu_editor'];
		if ( isset($menuEditor) && method_exists($menuEditor, 'get_plugin_option') ) {
			/** @var WPMenuEditor $menuEditor */
			$visibleUsers = $menuEditor->get_plugin_option('visible_users');
			if ( !empty($visibleUsers) && is_array($visibleUsers) ) {
				$loginsToInclude = array_unique(array_merge($loginsToInclude, $visibleUsers));
			}
		}

		foreach ($loginsToInclude as $login) {
			$user = get_user_by('login', $login);
			if ( empty($user) ) {
				continue;
			}

			$displayName = ($user->ID == $currentUser->ID) ? 'Current user' : $user->display_name;
			$actors['user:' . $login] = $displayName . ' (' . $user->user_login . ')';
		}

		//Reselect the previously selected actor, if any.
		$selectedActor = isset($this->get['selected_actor']) ? strval($this->get['selected_actor']) : null;
		if ( ($selectedActor !== null) && !array_key_exists($selectedActor, $actors) ) {
			$selectedActor = null;
		}

		require WS_ADMIN_BAR_EDITOR_DIR . '/templates/editor-page.php';
	}

	protected function getEditorPageUrl() {
		return admin_url(
			add_query_arg(
				['page' => $this->pageSlug],
				'options-general.php'
			)
		);
	}

	protected function actionSaveMenu() {
		$newNodes = json_decode($this->post['nodes'], true);
		if ( empty($newNodes) ) {
			$newNodes = json_decode($this->originalPost['nodes'], true);
		}

		if ( empty($newNodes) ) {
			$debugData = '';
			$debugData .= "Original POST:\n" . print_r($this->originalPost, true) . "\n\n";
			$debugData .= "Processed:\n" . print_r($this->post, true) . "\n\n";
			$debugData .= "\$_POST:\n" . print_r($_POST, true);

			$debugData = sprintf(
				"<textarea rows=\"30\" cols=\"100\">%s</textarea>",
				htmlentities($debugData)
			);
			wp_die('Invalid node data. Send this debugging information to the developer: <br>' . $debugData);
		}

		$this->saveCustomMenu($newNodes);

		wp_redirect(admin_url(
			add_query_arg(
				[
					'page'           => $this->pageSlug,
					'updated'        => 1,
					'selected_actor' => !empty($this->post['selected_actor']) ? strval($this->post['selected_actor']) : null,
				],
				'options-general.php'
			)
		));
	}

	protected function actionExportMenu() {
		if ( !isset($this->post['export_data']) || empty($this->post['export_data']) ) {
			die("Error: The 'export_data' field is empty or missing.");
		}

		$exportData = $this->post['export_data'];

		//Include the blog's domain name in the export filename to make it easier to
		//distinguish between multiple export files.
		$domain = @parse_url(site_url(), PHP_URL_HOST);
		if ( empty($domain) ) {
			$domain = '';
		}

		$exportFileName = trim(sprintf(
			'%s toolbar (%s).json',
			$domain,
			date('Y-m-d')
		));

		//Force file download
		header("Content-Description: File Transfer");
		header('Content-Disposition: attachment; filename="' . $exportFileName . '"');
		header("Content-Type: application/force-download");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: " . strlen($exportData));

		/* The three lines below basically make the download non-cacheable */
		header("Cache-control: private");
		header("Pragma: private");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

		echo $exportData;
		exit();
	}

	protected function actionImportMenu() {
		//An import file must be specified.
		if ( !isset($_FILES['import_file']) ) {
			$this->outputJsonForJqueryForm(['error' => 'No file specified.']);
			exit;
		}

		//Check for upload errors.
		if ( !empty($_FILES['import_file']['error']) ) {
			$this->outputJsonForJqueryForm([
				'error' => 'File upload failed. Error code: ' . $_FILES['import_file']['error'],
			]);
			exit;
		}

		//Sanity-check the file size. I expect import files will not exceed 1 MB in practice.
		$size = filesize($_FILES['import_file']['tmp_name']);
		if ( $size > self::MAX_IMPORT_FILE_SIZE ) {
			$this->outputJsonForJqueryForm(['error' => 'File too large.']);
			exit;
		} else if ( $size == 0 ) {
			$this->outputJsonForJqueryForm(['error' => 'You can not import an empty file.']);
			exit;
		}

		//Validate the file contents. It must be a valid JSON document.
		$importData = file_get_contents($_FILES['import_file']['tmp_name']);
		$json = json_decode($importData);
		if ( $json === null ) {
			$this->outputJsonForJqueryForm(['error' => 'Unknown file format. This is not a valid JSON document.']);
			exit;
		}

		$this->outputJsonForJqueryForm($json);
		die();
	}

	protected function actionSaveSettings() {
		$this->loadSettings();

		//Plugin access setting.
		$validAccessSettings = ['super_admin', 'manage_options', 'specific_user'];
		if ( isset($this->post['plugin_access']) && in_array($this->post['plugin_access'], $validAccessSettings) ) {
			$this->settings['plugin_access'] = $this->post['plugin_access'];

			if ( $this->settings['plugin_access'] === 'specific_user' ) {
				$this->settings['allowed_user_id'] = get_current_user_id();
			} else {
				$this->settings['allowed_user_id'] = null;
			}
		}

		//Configuration scope.
		$validScopes = ['global', 'site'];
		if ( isset($this->post['menu_config_scope']) && in_array($this->post['menu_config_scope'], $validScopes) ) {
			$this->settings['menu_config_scope'] = $this->post['menu_config_scope'];

			//On multisite it is also possible to override the global toolbar
			//configuration on a per-site basis.
			if ( $this->settings['menu_config_scope'] === 'global' ) {
				$override = isset($this->post['override_scope']) && !empty($this->post['override_scope']);
				if ( $override ) {
					update_option(self::MENU_SCOPE_OVERRIDE_OPTION, true);
				} else {
					delete_option(self::MENU_SCOPE_OVERRIDE_OPTION);
				}
			}
		}

		//Node detection.
		$this->settings['node_detection_enabled'] = !empty($this->post['node_detection_enabled']);

		$this->saveSettings();
		wp_redirect(add_query_arg('updated', 1, $this->getSettingsPageUrl()));
	}

	/**
	 * Utility method that outputs data in a format suitable to the jQuery Form plugin.
	 *
	 * Specifically, the docs recommend enclosing JSON data in a <textarea> element if
	 * the request was not sent by XMLHttpRequest. This is because the plugin uses IFrames
	 * in older browsers, which supposedly causes problems with JSON responses.
	 *
	 * @param mixed $data Response data. It will be encoded as JSON and output to the browser.
	 */
	private function outputJsonForJqueryForm($data) {
		$response = json_encode($data);

		$isXhr = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
		if ( !$isXhr ) {
			$response = '<textarea>' . $response . '</textarea>';
		}

		echo $response;
	}

	public function enqueueScripts() {
		wp_register_auto_versioned_script(
			'knockout',
			plugins_url('js/knockout-3.5.1.js', WS_ADMIN_BAR_EDITOR_FILE)
		);
		wp_register_auto_versioned_script(
			'jquery-json',
			plugins_url('js/jquery.json-2.4.js', WS_ADMIN_BAR_EDITOR_FILE),
			['jquery']
		);
		wp_register_auto_versioned_script(
			'jquery-ajax-form',
			plugins_url('js/jquery.form.js', WS_ADMIN_BAR_EDITOR_FILE),
			['jquery']
		);
		wp_register_auto_versioned_script(
			'jquery-qtip2',
			plugins_url('js/qtip/jquery.qtip.min.js', WS_ADMIN_BAR_EDITOR_FILE),
			['jquery']
		);
		wp_register_auto_versioned_script(
			'mjs-jquery-nested-sortable',
			plugins_url('js/jquery.mjs.nestedSortable.js', WS_ADMIN_BAR_EDITOR_FILE),
			['jquery-ui-sortable']
		);
		wp_register_auto_versioned_script(
			'ws-abe-nested-sortable',
			plugins_url('js/knockout-nested-sortable.js', WS_ADMIN_BAR_EDITOR_FILE),
			['knockout', 'mjs-jquery-nested-sortable']
		);

		wp_register_auto_versioned_script(
			'ws-abe-node-view-model',
			plugins_url('js/node-view-model.js', WS_ADMIN_BAR_EDITOR_FILE),
			['knockout', 'jquery']
		);

		wp_register_auto_versioned_script(
			'ws-abe-settings-script',
			plugins_url('js/settings-page.js', WS_ADMIN_BAR_EDITOR_FILE),
			['jquery']
		);

		if ( $this->isEditorPage() ) {
			wp_enqueue_auto_versioned_script(
				'ws-admin-bar-editor',
				plugins_url('js/admin-bar-editor.js', WS_ADMIN_BAR_EDITOR_FILE),
				[
					'jquery',
					'knockout',
					'jquery-json',
					'jquery-ajax-form',
					'jquery-ui-dialog',
					'mjs-jquery-nested-sortable',
					'ws-abe-nested-sortable',
					'ws-abe-node-view-model',
					'jquery-qtip2',
				]
			);
		} else if ( $this->isSettingsPage() ) {
			wp_enqueue_script('ws-abe-settings-script');
		}

		if ( $this->isDemoMode() ) {
			wp_register_auto_versioned_script(
				'ws-abe-jquery-cookie',
				plugins_url('js/jquery-cookie/jquery.cookie.js', WS_ADMIN_BAR_EDITOR_FILE)
			);
			wp_enqueue_auto_versioned_script(
				'ws-abe-demo-helper',
				plugins_url('js/demo.js', WS_ADMIN_BAR_EDITOR_FILE),
				['ws-abe-jquery-cookie']
			);
		}
	}

	public function enqueueStyles() {
		wp_register_auto_versioned_style(
			'abe-jquery-ui',
			plugins_url('css/smoothness/jquery-ui.min.css', WS_ADMIN_BAR_EDITOR_FILE)
		);
		wp_register_auto_versioned_style(
			'abe-jquery-ui-theme',
			plugins_url('css/smoothness/jquery.ui.theme.css', WS_ADMIN_BAR_EDITOR_FILE),
			['abe-jquery-ui']
		);
		wp_register_auto_versioned_style(
			'jquery-qtip2-styles',
			plugins_url('js/qtip/jquery.qtip.min.css', WS_ADMIN_BAR_EDITOR_FILE)
		);

		wp_enqueue_auto_versioned_style(
			'ws-admin-bar-editor-style',
			plugins_url('css/admin-bar-editor.css', WS_ADMIN_BAR_EDITOR_FILE),
			['abe-jquery-ui', 'abe-jquery-ui-theme', 'jquery-qtip2-styles', 'dashicons']
		);
	}

	/**
	 * Check if the current page is the "Toolbar Editor" admin page.
	 *
	 * @return bool
	 */
	protected function isEditorPage() {
		return is_admin()
			&& isset($this->get['page']) && ($this->get['page'] == $this->pageSlug)
			&& (!isset($this->get['sub_section']) || empty($this->get['sub_section']));
	}

	/**
	 * Check if the current page is the "Settings" sub-section of our admin page.
	 *
	 * @return bool
	 */
	protected function isSettingsPage() {
		return is_admin()
			&& isset($this->get['sub_section']) && ($this->get['sub_section'] == 'settings')
			&& isset($this->get['page']) && ($this->get['page'] == $this->pageSlug);
	}

	public function displaySettingsPage() {
		$this->loadSettings();

		//These variables are used by the template.
		$settings = $this->settings;
		$editorPageUrl = $this->getEditorPageUrl();
		$settingsPageUrl = $this->getSettingsPageUrl();

		require WS_ADMIN_BAR_EDITOR_DIR . '/templates/settings-page.php';
	}

	protected function getSettingsPageUrl() {
		return add_query_arg(
			['sub_section' => 'settings'],
			$this->getEditorPageUrl()
		);
	}

	protected function addAllContextualNodes($defaultNodes) {
		$extraNodes = self::getContextualNodes();

		foreach ($extraNodes as $node) {
			if ( !isset($defaultNodes[$node['id']]) ) {
				$after = isset($node['after']) ? $node['after'] : null;
				unset($node['after']);
				$node = (object)$node;

				if ( $after !== null ) {
					$defaultNodes = $this->insertAfter(
						$defaultNodes,
						$after,
						[$node->id => $node]
					);
				} else {
					$defaultNodes[$node->id] = $node;
				}
			}
		}

		return $defaultNodes;
	}

	public static function getContextualNodes(): array {
		//Most of these represent menus that get created in /wp-includes/admin-bar.php.
		return [
			[
				'after'  => 'logout',
				'parent' => 'top-secondary',
				'id'     => 'search',
				'title'  => '[Search Form]',
				'meta'   => [
					'class'    => 'admin-bar-search',
					'tabindex' => -1,
				],
			],

			[
				'after' => 'new-content',
				'id'    => 'view',
				'title' => 'View Item',
				'href'  => '[post or page URL]',
			],

			[
				'after' => 'new-content',
				'id'    => 'edit',
				'title' => 'Edit Item',
				'href'  => '[post editor URL]',
			],

			[
				'parent' => 'site-name',
				'id'     => 'dashboard',
				'title'  => __('Dashboard'),
				'href'   => admin_url(),
			],

			[
				'after'  => 'dashboard',
				'parent' => 'site-name',
				'id'     => 'appearance',
				'group'  => true,
			],

			[
				'after' => 'site-name',
				'id'    => 'customize',
				'title' => __('Customize'),
				'href'  => '[theme customizer]',
				'meta'  => [
					'class' => 'hide-if-no-customize',
				],
			],

			[
				'after' => 'site-name',
				'id'    => 'updates',
				'title' => 'Updates',
				'href'  => network_admin_url('update-core.php'),
				'meta'  => [
					'title' => '[update count]',
				],
			],

			['parent' => 'appearance', 'id' => 'themes', 'title' => __('Themes'), 'href' => admin_url('themes.php')],

			['parent' => 'appearance', 'id' => 'widgets', 'title' => __('Widgets'), 'href' => admin_url('widgets.php')],
			['parent' => 'appearance', 'id' => 'menus', 'title' => __('Menus'), 'href' => admin_url('nav-menus.php')],
			[
				'parent' => 'appearance',
				'id'     => 'background',
				'title'  => __('Background'),
				'href'   => admin_url('themes.php?page=custom-background'),
			],
			[
				'parent' => 'appearance',
				'id'     => 'header',
				'title'  => __('Header'),
				'href'   => admin_url('themes.php?page=custom-header'),
			],
		];
	}

	/**
	 * Insert one or more elements into an associative array after a specific key.
	 *
	 * If the input array does not contain the specified key this function
	 * will simply append the new elements to the end of the array.
	 *
	 * @param array $input
	 * @param string $key   Insert items after this key.
	 * @param array $insert The list of items to insert into the array.
	 * @return array Modified input array.
	 */
	protected function insertAfter($input, $key, $insert) {
		if ( !array_key_exists($key, $input) ) {
			return array_merge($input, $insert);
		}
		$index = array_search($key, array_keys($input));
		if ( $index === false ) {
			return array_merge($input, $insert);
		}

		return array_slice($input, 0, $index + 1, true)
			+ $insert
			+ array_slice($input, $index + 1, null, true);
	}

	/**
	 * Add AME Pro license data to update requests.
	 *
	 * @param array $queryArgs
	 * @return array
	 */
	public function filterUpdateChecks($queryArgs) {
		if ( $this->ameLicenseManager->getSiteToken() !== null ) {
			$queryArgs['license_token'] = $this->ameLicenseManager->getSiteToken();
		}
		$queryArgs['license_site_url'] = $this->ameLicenseManager->getSiteUrl();
		return $queryArgs;
	}

	/**
	 * Add license data to the update download URL if we have a valid license,
	 * or remove the URL (thus disabling one-click updates) if we don't.
	 *
	 * @param Update $pluginInfo
	 * @return Update
	 */
	public function filterUpdateDownloadUrl($pluginInfo) {
		if ( isset($pluginInfo, $pluginInfo->download_url) && !empty($pluginInfo->download_url) ) {
			$license = $this->ameLicenseManager->getLicense();
			if ( $license->isValid() ) {
				//Append license data to the download URL so that the server can verify it.
				$args = array_filter([
					'license_key'      => $this->ameLicenseManager->getLicenseKey(),
					'license_token'    => $this->ameLicenseManager->getSiteToken(),
					'license_site_url' => $this->ameLicenseManager->getSiteUrl(),
				]);
				$pluginInfo->download_url = add_query_arg($args, $pluginInfo->download_url);
			} else {
				//No downloads without a license!
				$pluginInfo->download_url = null;
			}
		}
		return $pluginInfo;
	}

	/**
	 * Get a list of all roles defined on this site.
	 *
	 * @return array Associative array of role names indexed by role ID/slug.
	 */
	private static function getRoleNames() {
		global $wp_roles;
		if ( !isset($wp_roles) ) {
			$wp_roles = new WP_Roles();
		}

		$roles = [];
		if ( isset($wp_roles->roles) ) {
			foreach ($wp_roles->roles as $role_id => $role) {
				$roles[$role_id] = $role['name'];
			}
		}

		return $roles;
	}

	/**
	 * WPML support: Update strings that need translation.
	 *
	 * @param Abe_Node[] $oldMenu The old custom menu, if any.
	 * @param Abe_Node[] $newMenu The new custom menu.
	 */
	private function updateWpmlStrings($oldMenu, $newMenu) {
		if ( !function_exists('icl_register_string') ) {
			return;
		}

		$previousStrings = $this->getWpmlStrings($oldMenu);
		$newStrings = $this->getWpmlStrings($newMenu);

		//Delete strings that are no longer valid.
		if ( function_exists('icl_unregister_string') ) {
			$removedStrings = array_diff_key($previousStrings, $newStrings);
			foreach ($removedStrings as $name => $value) {
				icl_unregister_string(self::WPML_CONTEXT, $name);
			}
		}

		//Register/update the new menu strings.
		foreach ($newStrings as $name => $value) {
			icl_register_string(self::WPML_CONTEXT, $name, $value);
		}
	}

	/**
	 * Prepare WPML translation strings for all node titles in the specified menu. Includes only custom titles.
	 *
	 * @param Abe_Node[] $customMenu
	 * @return array Associative array of strings that can be translated, indexed by unique name.
	 */
	private function getWpmlStrings($customMenu) {
		if ( empty($customMenu) ) {
			return [];
		}

		$strings = [];
		foreach ($customMenu as $node) {
			if ( !$node->group && $node->hasCustomSetting('title') ) {
				$strings[$node->getWpmlName()] = $node->title;
			}
		}
		return $strings;
	}

	private function isDemoMode() {
		return defined('IS_DEMO_MODE') && constant('IS_DEMO_MODE');
	}

	private function displayDemoNotice() {
		printf(
			'<div class="updated" id="abe-demo-notice">
			 <p>
				<a href="https://adminmenueditor.com/toolbar-editor/">Toolbar Editor</a>
				is an optional add-on that is included for free
				with the "Agency" license. You can also purchase it separately.

			  	&mdash; <a href="#" id="ws-abe-hide-demo-notice">Hide this notice.</a>
			  </p>
			  </div>'
		);
	}

	/**
	 * @param \YahnisElsts\AdminMenuEditor\EasyHide\HideableItemStore $store
	 */
	public function registerHideableItems($store) {
		if ( !empty($this->mergedNodes) ) {
			//This usually won't work because the Toolbar is not initialised yet
			//when this hook runs.
			$nodes = $this->mergedNodes;
		} else {
			$nodes = $this->loadCustomMenu();
		}

		if ( empty($nodes) ) {
			return;
		}

		$allRoleActors = self::getAllRoleActors();

		$cat = $store->getOrCreateCategory(
			'tbe',
			'Toolbar',
			null,
			true
		);

		foreach ($nodes as $node) {
			self::addHideableItem($store, $node, $cat, $nodes, $allRoleActors);
		}
	}

	/**
	 * @param Abe_Node $node
	 * @return string
	 */
	private static function makeHideableItemId($node) {
		return self::HIDEABLE_ITEM_PREFIX . $node->id;
	}

	/**
	 * @param \YahnisElsts\AdminMenuEditor\EasyHide\HideableItemStore $store
	 * @param \Abe_Node $node
	 * @param $category
	 * @param \Abe_Node[] $allNodes
	 * @param string[] $allRoles
	 * @return \YahnisElsts\AdminMenuEditor\EasyHide\HideableItem|null
	 */
	private static function addHideableItem($store, $node, $category, $allNodes, $allRoles) {
		$parent = null;
		if ( !empty($node->parent) && isset($allNodes[$node->parent]) && ($node->parent !== $node->id) ) {
			$parentNode = $allNodes[$node->parent];
			$hideableParentId = self::makeHideableItemId($parentNode);

			//Usually, the parent item will already exist at this point because the list
			//is sorted in depth-first order when saving settings. However, let's not rely
			//on that. We can recursively create the required parent(s).
			$parent = $store->getItemById($hideableParentId);
			if ( empty($parent) ) {
				$parent = self::addHideableItem($store, $parentNode, $category, $allNodes, $allRoles);
			}
		}

		$enabled = isset($node->is_visible_to_actor) ? $node->is_visible_to_actor : [];
		if ( $node->is_hidden ) {
			//The node is hidden from everyone, including any new roles and users that might
			//be added in the future. The EasyHide module doesn't support this, but we can
			//partially emulate it by setting all roles to false.
			$enabled = array_fill_keys($allRoles, false);
		}

		$label = $node->group ? $node->id : $node->title;

		//Remove icons and display-only text from the label. It's usually duplicated stuff,
		//like the "updates" node having the number of updates in two different places.
		if ( stripos($label, 'aria-hidden') !== false ) {
			$label = preg_replace(
				'@<span[^<>]+aria-hidden=[\'"]true[\'"][^<>]*+>[^<>]*+</span>@i',
				'',
				$label
			);
		}
		$label = strip_tags($label);

		//Make sure that the label is never empty or too short.
		if ( strlen($label) <= 2 ) {
			$label .= ' [' . $node->id . ']';
		}

		return $store->addItem(
			self::makeHideableItemId($node),
			$label,
			[$category],
			$parent,
			$enabled,
			self::HIDEABLE_COMPONENT_ID,
			$node->id
		);
	}

	private static function getAllRoleActors() {
		global $wp_roles;
		$allRoleActors = [];
		foreach ($wp_roles->roles as $roleId => $unused) {
			$allRoleActors[] = 'role:' . $roleId;
		}
		return $allRoleActors;
	}

	public function saveHideableItems($errors, $items) {
		$customNodes = $this->loadCustomMenu();
		$roleActorIds = array_fill_keys(self::getAllRoleActors(), false);
		$anySettingsModified = false;

		foreach ($customNodes as $node) {
			$id = self::makeHideableItemId($node);
			if ( isset($items[$id]) ) {
				$newEnabled = isset($items[$id]['enabled']) ? $items[$id]['enabled'] : [];
				$oldEnabled = isset($node->is_visible_to_actor) ? $node->is_visible_to_actor : [];
				$nodeModified = false;

				if ( $node->is_hidden ) {
					$isStillHidden = true;

					//Is the node still not explicitly enabled for anyone?
					foreach ($newEnabled as $isVisible) {
						if ( $isVisible ) {
							$isStillHidden = false;
							break;
						}
					}

					//Is the node still hidden from all roles? The default is to leave
					//a node visible, so a missing role would mean that it's no longer
					//globally hidden.
					$rolesWithSettings = array_intersect_key($roleActorIds, $newEnabled);
					if ( count($rolesWithSettings) < count($roleActorIds) ) {
						$isStillHidden = false;
					}

					if ( !$isStillHidden ) {
						$node->is_hidden = false;
						$nodeModified = true;
					}
				} else {
					$nodeModified = !self::areAssocArraysEqual($newEnabled, $oldEnabled);
				}

				if ( $nodeModified ) {
					$node->is_visible_to_actor = $newEnabled;
				}

				$anySettingsModified = $anySettingsModified || $nodeModified;
			}
		}

		if ( !empty($customNodes) && $anySettingsModified ) {
			$this->saveCustomMenu($customNodes);
		}

		return $errors;
	}

	/**
	 * Check if two arrays have the same keys and values. Method copied from AME.
	 *
	 * @param array $a
	 * @param array $b
	 * @return bool
	 */
	private static function areAssocArraysEqual($a, $b) {
		$secondArraySize = count($b);
		if ( count($a) !== $secondArraySize ) {
			return false;
		}
		$sameItems = array_intersect_assoc($a, $b);
		return count($sameItems) === $secondArraySize;
	}

	/**
	 * Dump an ID lookup map for the core nodes to a PHP file. This can later be used
	 * to skip built-in nodes when storing detected nodes in the database.
	 *
	 * @param array $defaultNodes
	 * @throws \Exception
	 * @noinspection PhpUnusedPrivateMethodInspection Used in development and only as needed.
	 */
	private function dumpCoreNodeList(array $defaultNodes) {
		//This function should only ever be called in development mode.
		if ( !defined('WP_DEBUG') || !WP_DEBUG ) {
			throw new Exception('This function should only be called in development mode.');
		}
		//There should be no other active plugins because they might add their own nodes.
		//Only this plugin and the main plugin (Admin Menu Editor Pro) should be active.
		$plugins = wp_get_active_and_valid_plugins();
		if ( count($plugins) > 2 ) {
			throw new Exception('This function should only be called when no other plugins are active.');
		}

		$nodeMap = [];
		foreach ($defaultNodes as $node) {
			//Skip "ame-..." nodes that might be added by AME Pro.
			if ( strpos($node->id, 'ame-') === 0 ) {
				continue;
			}

			$nodeMap[$node->id] = true;
		}

		//Alpha-sort for easier readability (debugging).
		ksort($nodeMap);

		file_put_contents(
			__DIR__ . '/../includes/core-nodes.php',
			'<?' . 'php return ' . var_export($nodeMap, true) . ';'
		);
	}

	public function saveCurrentMergedNodes() {
		//Sanity check.
		if ( empty($this->mergedNodes) || (count($this->mergedNodes) < 3) ) {
			return;
		}
		$tempDir = get_temp_dir();
		if ( empty($tempDir) ) {
			return;
		}
		$lockFilePath = trailingslashit($tempDir) . 'ws-abe-conf-upd.lock';

		$handle = @fopen($lockFilePath, 'a+');
		if ( $handle === false ) {
			//error_log('Failed to open lock file: ' . $lockFilePath);
			return;
		}
		//Get an exclusive lock.
		if ( !flock($handle, LOCK_EX | LOCK_NB) ) {
			//error_log('Failed to acquire lock on file: ' . $lockFilePath);
			fclose($handle);
			return;
		}

		//PHP docs warn that on some systems flock() is implemented at the process level.
		//This likely means that multiple instances of this script running as *threads*
		//in the same process would be able to acquire the same lock. To prevent this,
		//let's check if another instance has already written to the lock file recently.
		fseek($handle, 0);
		$storedTimestamp = @fread($handle, 30);
		if ( !empty($storedTimestamp) ) {
			$storedTimestamp = intval($storedTimestamp);
			if ( ($storedTimestamp > 0) && ((time() - $storedTimestamp) < 120) ) {
				//Another instance has acquired this lock recently. Abort.
				/*error_log(sprintf(
					'Another instance acquired the lock %d seconds ago, at %s. Aborting.',
					time() - $storedTimestamp,
					date('c', $storedTimestamp)
				));*/
				flock($handle, LOCK_UN);
				fclose($handle);
				return;
			}
		}

		$myTimestamp = (string)time();
		fwrite($handle, $myTimestamp);
		fflush($handle);
		//error_log('Acquired lock on file, timestamp written: ' . $myTimestamp);

		//Save the current merged configuration. It will include newly detected nodes
		//and updated "last seen" timestamps for existing nodes.
		$this->saveCustomMenu($this->mergedNodes);

		//Release the lock.
		//error_log('Releasing lock on file.');
		ftruncate($handle, 0);
		flock($handle, LOCK_UN);
		fclose($handle);
		@unlink($lockFilePath);
		//error_log('Lock released.');
	}

	private function isNodeDetectionEnabled(): bool {
		$this->loadSettings();
		return !empty($this->settings['node_detection_enabled']);
	}

	/**
	 * Can the specified node be included in the saved configuration?
	 *
	 * Most nodes are included, but there are some dynamic nodes that should be excluded
	 * to avoid overfilling the database with unnecessary data.
	 *
	 * @param array|\Abe_Node $node
	 * @return bool
	 */
	private function canIncludeInConfiguration($node): bool {
		if ( is_array($node) ) {
			$parent = $node['parent'] ?? null;
			$id = $node['id'] ?? null;
		} else {
			$parent = $node->parent ?? null;
			$id = $node->id ?? null;
		}

		//Exclude per-site dashboard links that only appear in Multisite. There's
		//a potentially unlimited number of them, it would be wasteful to store them all,
		//and bad UX to show a huge list of them in the editor.
		if (
			($parent === 'my-sites-list')
			|| preg_match('/^blog-\d++($|-)/', $id)
		) {
			return false;
		}
		return true;
	}
}

