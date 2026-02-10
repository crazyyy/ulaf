<?php
/*
Plugin Name: Plugins Genius
Plugin URI: http://www.marcocanestrari.it
Description: Role-based active plugins manager
Version: 2.1.1
Author: Marco Canestrari
Author URI: http://www.marcocanestrari.it
License: GPL2
*/

// some definition we will use
define( 'PG_PLUGIN_DIRECTORY', 'plugins-genius');
define( 'PG_GENIUS_LOCALE', 'pg_language' );

if(!function_exists('wp_get_current_user')) {
	// Load pluggable functions. Need current user info
	require( ABSPATH . WPINC . '/pluggable.php' );
	require( ABSPATH . WPINC . '/pluggable-deprecated.php' );
}


// register activation/deactivation hooks
register_activation_hook(__FILE__, array( 'PluginsGenius', 'activate' ));
register_deactivation_hook(__FILE__, array( 'PluginsGenius', 'deactivate' ));
register_uninstall_hook(__FILE__, array( 'PluginsGenius', 'uninstall' ));

if(get_option('pg_plugin_genius_active') && $_POST['pg_post_action'] != 'pg_restore_defaults') {
	add_filter('pre_option_active_plugins', array('PluginsGenius','active_plugins'));
}


// Load active plugins
$active = PluginsGenius::active_plugins(true);
foreach($active as $plugin) {

	wp_register_plugin_realpath( $plugin );
	include_once($plugin);
	if($_POST['pg_post_action'] == 'pg_save_new_settings') {

		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		do_action('activate_' . $plugin);


	}

}


class PluginsGenius {

	function __construct() {
		// localization
		$this->set_lang_file();

		// load configuration page
		add_action('pre_current_active_plugins', array($this,'plugin_page'));
		// add menu item
		add_action('admin_menu', array($this,'plugin_menu'));
		add_action('shutdown',array($this,'activate_me'),99);

	}


	public static function activate_me() {

		// PG must be the only active plugin in standard wp framework
		if(get_option('pg_plugin_genius_active') == 1) {
			$me_active = array();
			$me_active[] = PG_PLUGIN_DIRECTORY . "/plugins-genius.php";
			update_option('active_plugins',$me_active);
		}

	}

	public static function active_plugins($validate = false) {



		if(WP_ADMIN === true) {

			if($_POST['pg_post_action'] == 'pg_save_new_settings') {
				$my_active_plugins = $_POST['genius'];
			} else {
				$my_active_plugins = get_option('pg_my_genius_active_plugins');
			}

			if(!$my_active_plugins && get_option('pg_plugin_genius_active') == '0') {

				// if PG is first runnuning or has been reset
				if($validate) {
					$active = self::wp_get_active_and_valid_genius_plugins(get_option('active_plugins'));
				} else {
					$active = get_option('active_plugins');


				}



			} else {

				// if PG is set, load active plugin role-based
				$current_user = wp_get_current_user();

				$current_role = $current_user->roles[0];
				if(!$current_role) {
						$current_role = 'front';
				}

				// load role-based active plugins
				if($validate) {
					$active = self::wp_get_active_and_valid_genius_plugins($my_active_plugins[$current_role]);
				} else {
					$active = $my_active_plugins[$current_user->roles[0]];

				}



			}

		} else {

			$my_active_plugins = get_option('pg_my_genius_active_plugins');
			$active;
			if(!$my_active_plugins && get_option('pg_plugin_genius_active') == '0') {

				// if PG is first runnuning or has been reset
				if($validate) {
					$active = self::wp_get_active_and_valid_genius_plugins(get_option('active_plugins'));
				} else {
					$active = get_option('active_plugins');

				}



			} else {

				// load front-end active plugins
				if($validate) {
					$active = self::wp_get_active_and_valid_genius_plugins($my_active_plugins['front']);
				} else {
					$active = $my_active_plugins['front'];

				}


			}

		}

		if(basename($_SERVER['PHP_SELF']) == 'plugins.php') {
			$active[] = PG_PLUGIN_DIRECTORY . "/plugins-genius.php";
		}



		return $active;

	}


	// load language files
	function set_lang_file() {
		# set the language file
		$currentLocale = get_locale();
		if(!empty($currentLocale)) {
			$moFile = dirname(__FILE__) . "/lang/" . $currentLocale . ".mo";
			if (@file_exists($moFile) && is_readable($moFile)) {
				load_textdomain(PG_GENIUS_LOCALE, $moFile);
			}

		}
	}



	// Menu item
	function plugin_menu() {
		add_submenu_page( 'plugins.php', 'Plugins Genius', 'Plugins Genius', 'install_plugins', 'plugins.php?tab=genius' );
	}

	/*
	Configuration page, alters plugins page
	*/
	function plugin_page() {

		// show alert messages
		$this->settings_saved();

		// Set and load tabs
		$tab = isset($_GET['tab']) ? $_GET['tab'] : "plugins";
		$this->tabs($tab);


		switch ($tab) {

			case 'genius':
				// tab Genius: configure active plugins
				$this->config();
				break;

			default:
				// default plugin page, alter inactive plugins view
				if(get_option('pg_plugin_genius_active') == 1) {
					echo "<style>.activate, .deactivate {display:none;} .inactive, .update-message {opacity:0.5;} .inactive:hover, .update-message:hover {opacity:1;}</style>";
					echo '
					<script type="text/javascript">
					jQuery(document).ready(function(){
						jQuery("tr[data-slug=\'plugins-genius\'] .deactivate").css("display","inline");
					});

					</script>

					';
					}


				// alert message: PG is active
				echo '<div id="pg-warning" class="updated fade"><p><strong>' .__("Plugins Genius is active",PG_GENIUS_LOCALE ) .'</strong>. '.__("To configure active plugins, go to",PG_GENIUS_LOCALE ) .' <a href="?tab=genius">'.__("Plugins Genius configuration page",PG_GENIUS_LOCALE ) .'</a>.</p></div>';
		}
	}

	/*
	Shows alert messages
	*/
	function settings_saved() {

		if($_GET['pg_post_action']) {
			$action = $_GET['pg_post_action'];
		} elseif ($_POST['pg_post_action']) {
			$action = $_POST['pg_post_action'];
		}


		switch($action) {
			case 'pg_save_new_settings':
				echo '<div id="pg-warning" class="updated fade"><p><strong>Plugins Genius: </strong>'.__("new settings saved",PG_GENIUS_LOCALE ) .'.</p></div>';
				break;
			case 'pg_restore_defaults':
				echo '<div id="pg-warning" class="updated fade"><p><strong>Plugins Genius: </strong>'.__("original settings restored",PG_GENIUS_LOCALE ) .'.</p></div>';
				break;
		}


	}

	// Configure tabs
	function tabs($current = 'plugins') {
		$tabs = array('plugins' => __('Installed Plugins'), 'genius' => __('Plugins Genius', PG_GENIUS_LOCALE));
		$links = array();
		foreach( $tabs as $tab => $name ) {
			if ( $tab == $current ) {
				$links[] = "<a class='nav-tab nav-tab-active' href='?tab=".$tab."'>$name</a>";
			} else {
				$links[] = "<a class='nav-tab' href='?tab=".$tab."'>$name</a>";
			}
		}

		echo '<h3 class="nav-tab-wrapper">';
		foreach ( $links as $link ) {
			echo $link;
		}
		echo '</h3>';
	}

	/*
	PG Configuration page
	*/
	function config() {

		global $wp_roles;

		// Save settings
		if ($_POST['pg_post_action'] && current_user_can( 'manage_options' ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'pg_settings_saved' )) {

			switch($_POST['pg_post_action']) {
				case 'pg_save_new_settings':

					update_option('pg_my_genius_active_plugins',$_POST['genius']);
					update_option('pg_plugin_genius_active','1');
					wp_redirect(get_bloginfo('wpurl') . '/wp-admin/plugins.php?tab=genius&pg_post_action=pg_save_new_settings');
					break;

				case 'pg_restore_defaults':

					delete_option('pg_my_genius_active_plugins');
					delete_option('pg_plugin_genius_active');
					$active_plugins = get_option('pg_old_active_plugins');
					$active_plugins[] = PG_PLUGIN_DIRECTORY . "/plugins-genius.php" ;
					update_option('active_plugins',$active_plugins);
					remove_action('shutdown',array('PluginsGenius','activate_me'),99);
					wp_redirect(get_bloginfo('wpurl') . '/wp-admin/plugins.php?tab=genius&pg_post_action=pg_restore_defaults');
					break;
			}

		}



		// Hide standard plugins page
		echo '<style>.plugins, .tablenav, .subsubsub, .search-box {display:none;} .pg_counter {margin-left: 1px;}</style>';

		// Load configuration
		$my_genius_active_plugins = get_option('pg_my_genius_active_plugins');

		echo '	<div style="float:right;">
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
					<input type="hidden" name="cmd" value="_s-xclick">
					<input type="hidden" name="hosted_button_id" value="WGVV5FF7AKRBQ">
					<input type="image" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal Ð The safer, easier way to pay online.">
					<img alt="" border="0" src="https://www.paypalobjects.com/it_IT/i/scr/pixel.gif" width="1" height="1">
				</form>
			</div>';

		// prepare table
		$all_plugins = get_plugins();

		echo '
		<h2>'. __("Manage plugins loading",PG_GENIUS_LOCALE ) .'</h2>
		<h3>'. __("Select plugins to load for each role or in the frontend",PG_GENIUS_LOCALE ) .'</h3>
		<form action="" method="post">
		<table class="wp-list-table widefat pg-table">
		<thead><tr><th></th><th class="manage-column column-description">'. __('Installed Plugins') .' ('.(count($all_plugins) -1).')</th>';

		// Roles
		foreach( $wp_roles->role_names as $role => $name ) {

			$count;

			if(!$my_genius_active_plugins && get_option('pg_plugin_genius_active') == '0') {
				$count = count(get_option('active_plugins')) - 1;
			} else {
				if($my_genius_active_plugins) {
					$count = count($my_genius_active_plugins[$role],0);
				} else {
					$count = '0';
				}

			}

			echo '<th style="text-align:center;" class="manage-column column-description">'.translate_user_role($name).'<br><small class="pg_counter">('.$count.' '.__('active',PG_GENIUS_LOCALE).')</small></th>';


		}

		// Front-end
		$count;

		if(!$my_genius_active_plugins && get_option('pg_plugin_genius_active') == '0') {
			$count = count(get_option('active_plugins')) - 1;
		} else {
			if($my_genius_active_plugins) {
				$count = count($my_genius_active_plugins['front'],0);
			} else {
				$count = '0';
			}

		}
		echo '<th style="text-align:center;" class="manage-column column-description">'. __("Frontend",PG_GENIUS_LOCALE ) .'<br><small class="pg_counter">('.$count.' '.__('active',PG_GENIUS_LOCALE).')</small></th>';


		echo '</tr></thead>';

		// Select all checkboxes
		// Main
		echo '<tr style="background:#eee;"><td><input type="checkbox" id="pg_checkbox" onclick="selectAllInput(\'pg_checkbox\')"/></td><td></td>';

		// Roles
		$cols = 0;
		foreach( $wp_roles->role_names as $role => $name ) {

			echo '<td style="text-align:center;"><input type="checkbox" class="pg_checkbox" id="col_'.$cols.'" onclick="selectAllInput(\'col_\'+'.$cols.')"/></td>';
			$cols++;

		}
		echo '<td style="text-align:center;"><input type="checkbox" class="pg_checkbox" id="col_'.$cols.'" onclick="selectAllInput(\'col_\'+'.$cols.')"/></td>';

		echo '</tr>';

		$rows = 0;

		// Iterate plugins
		foreach($all_plugins as $key => $plugin) {

			if($key != "plugins-genius/plugins-genius.php") {

				// Select all checkboxes: for plugin
				echo '<tr><td><input type="checkbox" class="pg_checkbox" id="row_'.$rows.'" onclick="selectAllInput(\'row_\'+'.$rows.')"/></td><td><strong>'.$plugin['Name'].'</strong></td></td>';
				$cols = 0;
				foreach( $wp_roles->role_names as $role => $name ) {
					$excluded = $my_genius_active_plugins[$role];
					if(!$my_genius_active_plugins && get_option('pg_plugin_genius_active') == '0') {
						$excluded = get_option('active_plugins');
					}
					$checked = '';
					if($excluded) {
						foreach($excluded as $p){
							if ($p == $key) {
								$checked = 'checked';
							}
						}
					}

					// Checkbox
					echo '<td style="text-align:center;" class="column-description desc"><input type="checkbox" '.$checked.' name="genius['.$role.'][]" value="'.$key.'" class="pg_checkbox row_'.$rows.' col_'.$cols.'"/> </td>';
					$cols++;
				}

				$excluded = $my_genius_active_plugins['front'];
				if(!$my_genius_active_plugins && get_option('pg_plugin_genius_active') == '0') {
					$excluded = get_option('active_plugins');
				}
				$checked = '';
				if($excluded) {
					foreach($excluded as $p){
						if ($p == $key) {
							$checked = 'checked';
						}
					}
				}

				echo '<td style="text-align:center;" class="column-description desc"><input type="checkbox" '.$checked.' name="genius[front][]" value="'.$key.'" class="pg_checkbox row_'.$rows.' col_'.$cols.'"/> </td>';
			}

			$rows++;

		}



		echo '<tfoot><tr><th></th><th class="manage-column column-description">'. __('Installed Plugins') .'</th>';

		foreach( $wp_roles->role_names as $role => $name ) {

			echo '<th style="text-align:center;" class="manage-column column-description">'.translate_user_role($name).'</th>';

		}
		echo '<th style="text-align:center;" class="manage-column column-description">'. __("Frontend",PG_GENIUS_LOCALE ) .'</th>';
		echo '</tr></tfoot>';
		echo '
		</table>
		<div class="clear" style="margin-top: 10px;"></div>
		<input type="hidden" name="pg_post_action" value="pg_save_new_settings">
		<input type="hidden" name="_wpnonce" value="'.wp_create_nonce( 'pg_settings_saved' ).'" />
		<input type="submit" name="submit_button" class="button-primary" value="'.__('Save').'" />
		</form></p>

		<script type="text/javascript">

			function selectAllInput(myClass) {


				jQuery("."+myClass).each(function() {
					if(jQuery("#"+myClass).is(\':checked\')) {
						this.checked = true;
					} else {

						this.checked = false;
					}
				});
			}


		</script>
		<style>
		.pg-table td {border-bottom: 1px solid #ddd;}
		</style>';

		// Advanced: reset PG to old active plugins
		echo '
		<div class="metabox-holder">


			<div id="post-body-content">
				<div class="postbox meta-box-sortables ui-sortable" >
					<form action="" method="post">
						<h3 class="hndle"><span>'.__('Advanced').'</span></h3>

						<div class="inside">

							<table class="form-table">
								<tbody>
									<tr>
									    <th>'.__('Original settings',PG_GENIUS_LOCALE).'</th>
									    <td>

											<input type="submit"class="button-primary" value="'.__('Restore original settings',PG_GENIUS_LOCALE).'">
											<input type="hidden" name="pg_post_action" value="pg_restore_defaults">
											<input type="hidden" name="_wpnonce" value="'.wp_create_nonce( 'pg_settings_saved' ).'" />

												    <br><span class="description">'.__('Restore configuration as it was before activating Plugins Genius.',PG_GENIUS_LOCALE).'</span>
									    </td>
									</tr>
								</tbody>
							</table>

						</div>
					</form>
				</div>
			</div>



		</div>


		';

	}

	/*
	Verifies if plugins exists and can be loaded
	*/
	public function wp_get_active_and_valid_genius_plugins($active_plugins) {

		$plugins = array();

		// Check for hacks file if the option is enabled
		if ( get_option( 'hack_file' ) && file_exists( ABSPATH . 'my-hacks.php' ) ) {
			_deprecated_file( 'my-hacks.php', '1.5' );
			array_unshift( $plugins, ABSPATH . 'my-hacks.php' );
		}

		if ( empty( $active_plugins ) || defined( 'WP_INSTALLING' ) )
			return $plugins;

		$network_plugins = is_multisite() ? wp_get_active_network_plugins() : false;

		foreach ( $active_plugins as $plugin ) {
			if ( ! validate_file( $plugin ) // $plugin must validate as file
				&& '.php' == substr( $plugin, -4 ) // $plugin must end with '.php'
				&& file_exists( WP_PLUGIN_DIR . '/' . $plugin ) // $plugin must exist
				// not already included as a network plugin
				&& ( ! $network_plugins || ! in_array( WP_PLUGIN_DIR . '/' . $plugin, $network_plugins ) )
				)
			$plugins[] = WP_PLUGIN_DIR . '/' . $plugin;
		}
		$active[] = WP_PLUGIN_DIR . '/' . PG_PLUGIN_DIRECTORY . "/plugins-genius.php";
		return $plugins;
	}

	// activating the default values
	public static function activate() {
		$active_plugins = get_option('active_plugins');
		$key = array_search(PG_PLUGIN_DIRECTORY . "/plugins-genius.php", $active_plugins);
		if(false !== $key) {
			unset($active_plugins[$key]);
		}
		update_option('pg_old_active_plugins',$active_plugins);
		if(get_option('pg_my_genius_active_plugins')) {
			update_option('pg_plugin_genius_active','1');
		}


	}

	// deactivating
	public static function deactivate() {
		// needed for proper deletion of every option

		delete_option('pg_plugin_genius_active');
		add_action('shutdown',array('PluginsGenius','deactivating') );
		remove_action('shutdown',array('PluginGenius','activate_me'),99);

	}

	public static function deactivating() {
		$active_plugins = get_option('pg_old_active_plugins');
		$key = array_search(PG_PLUGIN_DIRECTORY . "/plugins-genius.php", $active_plugins);
		if(false !== $key) {
			unset($active_plugins[$key]);
		}
		update_option('active_plugins',$active_plugins);
	}

	// uninstalling
	public static function uninstall() {
		# delete all data stored
		delete_option('pg_plugin_genius_active');
		delete_option('pg_old_active_plugins');
		delete_option('pg_my_genius_active_plugins');
	}

}

new PluginsGenius();





?>
