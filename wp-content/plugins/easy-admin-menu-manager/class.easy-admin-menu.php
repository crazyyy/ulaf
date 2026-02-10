<?php
if ( !defined('ABSPATH') ) exit; //Exit if accessed directly

/* Easy Admin Menu Manager */
require_once ( EAMM_DIR . 'inc/eamm-defines.php' );
require_once ( EAMM_DIR . 'inc/eamm-ajax.php' );

class easy_admin_menu {
	protected $pluginloc;
	protected $currentPage = '';
	protected $isPluginPage = false;
    protected $logfile = array(
        'LOGFILE' => array('2022-10-12 00:00:00'=>'Activated')
    );
    protected $options = array(
	    'switches' => array(
			'Show Dashboard Info'=>1,
			//'Simplify the Admin Menu'=>1
	    ),
	    'items' => array(
		    'upload.php|#|'=>['id'=>'menu-media', 'n'=>'Media / Uploads', 'hide'=>0],
		    'edit-tags.php?taxonomy=link_category|#|'=>['id'=>'menu-links', 'n'=>'Links', 'hide'=>1],
		    'edit-comments.php|#|'=>['id'=>'menu-comments', 'n'=>'Comments Menu', 'hide'=>1],
		    'edit.php?post_type=project|#|'=>['id'=>'menu-posts-project', 'n'=>'Project Menu', 'hide'=>1],
		    'themes.php|#|'=>['id'=>'menu-appearance', 'n'=>'Themes Menu', 'hide'=>0],
		    'plugins.php|#|'=>['id'=>'menu-plugins', 'n'=>'Plugins Menu', 'hide'=>0],
		    'users.php|#|'=>['id'=>'menu-users', 'n'=>'Users Menu', 'hide'=>0],
	    ),
		'toggle' => 0,
	    'url' => ''
    );

	/**
	 * Initialise the plugin class
	 * @param string $loc the full directory and filename for the plugin
	 */
	public function __construct($loc) {
		$this->pluginloc = strlen($loc)? $loc: __FILE__;
		//check if the active page is the plugin
		$this->currentPage = isset($_GET['page'])? sanitize_text_field($_GET['page']): '';
		$this->isPluginPage = ( $this->currentPage == 'eamm_menu_page');
		$basename = plugin_basename($this->pluginloc);
		$options = get_option('eamm_options');
		//TODO: Remove the check below from the next version (3 lines)
		if (isset($options['switches']['Simplify the Admin Menu'])){
			unset($options['switches']['Simplify the Admin Menu']);
		}
		$this->options = is_array($options)? array_merge($this->options,$options): $this->options;
        $this->options['url'] = home_url();

		if (is_admin()){
			add_action( 'admin_enqueue_scripts', array($this, 'eamm_enqueue_admin') );
			add_action( 'admin_init',array($this, 'eamm_register_settings') );
			add_action( 'admin_menu', array($this, 'eamm_admin_menu'), 11 );
			add_filter( 'plugin_action_links_'.$basename, array($this, 'eamm_settings_link') );
			if (isset($this->options['switches']['Show Dashboard Info']) && $this->options['switches']['Show Dashboard Info']){
				add_action( 'wp_dashboard_setup', array($this, 'eamm_add_dashboard') );
			}
			if ($this->isPluginPage) {
				//update the list of menu options when the Easy Admin Menu link is active
				add_action( 'admin_menu', array($this, 'eamm_update_option'), 999 );
			}
			//TODO: hide the menus without the line below
			//add_action( 'admin_menu', array($this, 'eamm_hide_menus'), 999 );
			add_action( 'wp_ajax_eamm_toggle_option', 'eamm_toggle_option' );
			
			//manage the stored variable and option values when registering or deactivating
			register_activation_hook( $loc, array($this, 'eamm_load_options' ) );
			register_deactivation_hook( $loc, array($this, 'eamm_unset_options' ) );
			register_uninstall_hook ( $loc, array($this, 'eamm_uninstall') );
		} else {
			add_action('wp_enqueue_scripts', array($this, 'eamm_enqueue_main'));
		}
	}

	// -------------------- Add styles and scripts --------------------
	/**
	 * @param $hook - the admin_enqueue_scripts action provides the $hook_suffix for the current admin page.
	 * This is used to load the scripts only for the admin pages associated with the plugin
	 * HOOK: "toplevel_page_leads5050-code"
	 */
	function eamm_enqueue_main($hook){
		wp_enqueue_style('eamm-main', plugins_url('css/eamm.css', __FILE__));
		wp_enqueue_script('eamm-main', plugins_url(('js/eamm.js?x='.rand(5,300)), __FILE__), array('jquery'), '1.0', true);
	}

	function eamm_enqueue_admin(){
        wp_enqueue_style('eamm-admin-css', plugins_url('css/eamm-admin.css', __FILE__));
        wp_enqueue_script('eamm-admin-js', plugins_url(('js/eamm-admin.js?x='.rand(5,300)),
	        __FILE__), array('jquery'), '1.0', true);
		wp_localize_script('eamm-admin-js', 'eamm_x', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'hash'=>base64_encode(home_url()),
			'nonce'=>wp_create_nonce('eamm-nonce-576'),
		));
	}

	/**
	 * Late loading function for actions that runs after all plugins are loaded
	 */
	function eamm_late_loader(){

	}

	function eamm_update_option(){
		$this->options['items'] = update_toggle_list($this->options['items']);
		update_option('eamm_options', $this->options);
	}

	// -------------------- Options and Variables - Admin Settings Form Definition --------------------

	function eamm_admin_menu() {
		$menu_title = (isset($this->options['toggle']) && $this->options['toggle']==1)? 'Show Full Menu': 'Simplify Menu';
		add_menu_page( 'Easy Admin Menu', $menu_title, 'manage_options', 'eamm_menu_page',
			array($this,'eamm_options_page'), '',9999);
		add_submenu_page( 'options-general.php', 'Easy Admin Menu', 'Easy Admin Menu',
			'manage_options', 'eamm_menu_page', array($this, 'eamm_options_page') );
	}

	/**
	 * @param $links - When the 'plugin_action_links_(plugin file name)' filter is called, it is passed one parameter:
	 * namely the links to show on the plugins overview page in an array
	 * https://codex.wordpress.org/Plugin_API/Filter_Reference/plugin_action_links_(plugin_file_name)
	 *
	 * @return mixed
	 */
	function eamm_settings_link($links) {
		$url = get_admin_url().'options-general.php?page=eamm_menu_page';
		$settings_link = '<a href="'.$url.'">' . __("Settings") . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	function eamm_register_settings() {
		register_setting('eamm_group', 'eamm_options', array($this, 'eamm_validate'));
	}

	/**
	 * Validate and transform the values submitted to the options form. All inputs are switches and values are
	 * forced to 0 (off) or 1 (on) for the storage and display.
	 * @param array $input - options results from the form submission
	 * @return array|false - validated and transformed options results
	 */
	function eamm_validate($input){
        $output = array();
		foreach ( $this->options['switches'] as $type => $state ) {
			$output['switches'][$type] = (isset($input['switches'][$type]) && $input['switches'][$type])? 1: 0;
		}
		foreach ( $this->options['items'] as $att => $arr ) {
			$output['items'][$att] = $arr;
			$output['items'][$att]['hide'] = (isset($input['items'][$att]['hide']) && $input['items'][$att]['hide'])? 1: 0;
		}
		$output['toggle'] = (int) $input['toggle'];
		return $output;
	}

	function eamm_options_page() {
		global $menu, $submenu;
		$options = $this->options;
		if(current_user_can('manage_options')) {
			echo '<div class="wrap">';
				echo '<h2>Option Settings ['.esc_html(get_admin_page_title()).']</h2>';
				echo '<div id="eamm-main-form">';
				settings_errors();
					echo '<form action="options.php" method="post">';
						settings_fields('eamm_group'); //This line must be inside the form tags!!
						echo '<table class="form-table">';
							echo '<tr class="eamm-input-hdr"><th colspan="2">GENERAL SETTINGS</th></tr>';
							echo '<tr><th>Name</th><th>Toggle State</th></tr>';
							foreach ($options['switches'] as $name => $toggle) {
								echo '<tr><td style="width:30%;">'.esc_html($name).'</td><td>';
								echo '<label class="eamm_switch">';
								$disable = (in_array($name,array('External')))? 'disabled="disabled"': '';
								echo '<input name="eamm_options[switches]['.esc_html($name).']" value="1" type="checkbox" '.($toggle? "checked": "").' '.esc_attr($disable).'>';
								echo '<span class="eamm_slider"></span>';
								echo '</label>';
								echo '</td></tr>';
							}
						echo '</table>';
						echo '<table class="form-table">';
							echo '<tr class="eamm-input-hdr"><th colspan="2">MENU SWITCHES</th></tr>';
							echo '<tr><th>Name</th><th>Show/Hide</th></tr>';
							foreach ($options['items'] as $att => $arr) {
								echo '<tr><td style="width:30%;">'.esc_html($arr['n']).'</td><td>';
								echo '<label class="eamm_switch">';
								echo '<input name="eamm_options[items]['.esc_html($att).'][hide]" value="1" type="checkbox" '.($arr['hide']==1? "checked": "").'>';
								echo '<span class="eamm_slider"></span>';
								echo '</label>';
								echo '</td></tr>';
							}
						echo '</table>';
						echo '<input type="hidden" name="eamm_options[toggle]" value="'.esc_attr($options['toggle']).'" /></td></tr>';
						submit_button();
					echo '</form>';
				echo '</div>';
			echo '</div>';
			//echo "<h3>Options</h3><pre>".var_export($options, true)."</pre>";
			//echo '<h3>Menu</h3><pre>'.var_export($menu, true).'</pre><hr />';
			//echo '<h3>Submenu</h3><pre>'.var_export($submenu, true).'</pre>';
		} else {
			wp_die('You do not have sufficient permissions to access this page.');
		}
	}

	// -------------------- Dashboard Widget --------------------
	
	/**
	 * Display a widget on the main dashboard page to advise the user that Easy Admin Menu is active.
	 * Other action can later be included here.
	 * @return void
	 */
	public function eamm_add_dashboard(){
		wp_add_dashboard_widget ('eamm_dashboard_widget', 'Easy Admin Menu Manager',array($this, 'eamm_dashboard_widget'));
	}

	/**
	 * Include a notice that Easy Admin Menu Manager is active on the site and link this to the menu item.
	 * @return void
	 */
	public function eamm_dashboard_widget(){
		$logo = plugin_dir_url( __FILE__ ).(idate("m")==12? 'img/easy-notice-xmas.png': 'img/easy-notice.png');
		$url = get_admin_url().'admin.php?page=eamm_menu_page';
		echo '<div><a href="' . esc_url($url) . '" title="Go To Easy Admin Menu">'.
		     '<img alt="Go to the easy admin menu manager" src="' . esc_url($logo) . '"/></a></div>';
	}
	// -------------------- Clean Dashboard --------------------
	/**
	 * Remove menu items from the WordPress dashboard if the toggle is set to 1 (ON)
	 * A check is performed on the active menu to ensure that this cannot be hidden while it is active
	 * @return void
	 */
	function eamm_hide_menus(){
		$options = $this->options;
		//$options = get_option('eamm_options');
		$items = (is_array($options['items']) && count($options['items']))? $options['items']: [];
		if (isset($options['toggle']) && $options['toggle']==1){
			if ( $items ){
				$parentMenu = get_admin_page_parent(); //the parent menu slug
				//iterate through the options set and hide menus where 'hide' is set to 1
				foreach ( $items as $att=>$arr ) {
					$mnu = explode('|#|',$att);
					if ( is_array($arr) && isset($mnu[0]) && isset($mnu[1]) && $arr['hide']==1 ) {
						//do not hide the current / active menu or submenu items
						if ( $mnu[0]!=$this->currentPage && $mnu[0]!=$parentMenu ){
							if ( strlen($mnu[1]) ) {
								remove_submenu_page( $mnu[0], $mnu[1]);
							} else {
								remove_menu_page($mnu[0]);
							}
						}
					}
				}
			}
		}
	}
	// -------------------- Actions --------------------
	// -------------------- AJAX call function --------------------
	// -------------------- Set up the CRON jobs --------------------

	// -------------------- Define actions to be taken when installing and uninstalling the Plugin --------------------
	function eamm_load_options() {
		//$value = serialize($this->options);
		add_option('eamm_options', $this->options);
		add_option('eamm_log', $this->logfile);
	}

	function eamm_unset_options() {
        delete_option('eamm_options');
        delete_option('eamm_log');
		//Unschedule any CRON jobs
	}

	function eamm_uninstall() {
		delete_option('eamm_options');
		delete_option('eamm_log');
		//Unschedule any CRON jobs
	}
}

/**
 * Check that option values are up-to-date when the options page is loaded
 * $itm[0] is the menu text and $itm[2] is the menu link
 * $menuList holds a list of all current menus with the name [n=>] and toggle [hide=>]
 * @param $toggles - list op options set includes whether the menu item is hidden (1) or visible (0)
 * @param $incSubmenu - include switched on submenus (not in use at present)
 *
 * @return array
 */
function update_toggle_list($toggles, $incSubmenu=false): array {
	global $menu, $submenu;
	$menuList = [];
	//get the full menu list and set the default toggle to off
	foreach ( $menu as $i => $itm ) {
		if ( isset($itm[0]) && strlen($itm[0]) && isset($itm[2]) && $itm[2]!='eamm_menu_page' && $itm[2]!='index.php' ){
			$m = sanitize_text_field($itm[2]);
			$n = sanitize_text_field($itm[0]);
			$id = sanitize_text_field($itm[5]);
			$menuList[($m.'|#|')] = ['id'=>$id, 'n'=>$n, 'hide'=>0];
			if ($incSubmenu){
				if ( isset($submenu[$itm[2]]) && is_array($submenu[$itm[2]]) ){
					foreach ( $submenu[$itm[2]] as $j => $sub ) {
						if ( isset($sub[0]) && strlen($sub[0]) && isset($sub[2]) ){
							$s = sanitize_text_field($sub[2]);
							if ( $m!=$s ) {
								$menuList[($m.'|#|'.$s)] = ['n'=>sanitize_text_field($sub[0]), 'hide'=>0];
							}
						}
					}
				}
			}
		}
	}
	//update the toggles based on the original toggle list ($toggles)
	if (is_array($toggles)){
		foreach ( $toggles as $att => $tgl ) {
			if (isset($menuList[$att])){
				$menuList[$att]['hide'] = $tgl['hide'];
			}
		}
	}
	return $menuList;
}

