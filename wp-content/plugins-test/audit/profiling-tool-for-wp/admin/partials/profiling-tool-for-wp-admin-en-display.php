<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://https://www.dnsempresas.com/
 * @since      1.0.0
 *
 * @package    Profiling_Tool_For_Wp
 * @subpackage Profiling_Tool_For_Wp/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

$tabla = $wpdb->prefix . 'ptfwp_options';

$query = $wpdb->prepare( "SELECT * FROM {$tabla} WHERE ID = %d", 1 );

$options = $wpdb->get_row( $query, ARRAY_A );
	
switch( $options["LANGUAGE"]  ){
    case "EN":	
        include_once ( PROFILING_TOOL_FOR_WP_PATH . 'languages/en.php' );	
        break;
		
    case "ES":
        include_once ( PROFILING_TOOL_FOR_WP_PATH . 'languages/es.php' );
        break;
		
    case "GL":
        include_once ( PROFILING_TOOL_FOR_WP_PATH . 'languages/gl.php' );
        break; 
		
    default:
        include_once ( PROFILING_TOOL_FOR_WP_PATH . 'languages/es.php' );
		
}

update_option( PROFILING_TOOL_FOR_WP_LANGUAGE_OPTION, wp_json_encode( $lang ) );

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap patp-wrap patp">
    <h1 class="wp-heading-inline"><?php esc_html_e('Measure Page Performance', 'profiling-tool-for-wp'); ?></h1>
    <br /><br />

    <!-- Tab links -->
    <div class="tab">
      <button class="tablinks" onclick="openTab(event, 'pluginProfile')" id="defaultOpen"><?php esc_html_e('Plugins and themes', 'profiling-tool-for-wp'); ?></button>  
      <button class="tablinks" onclick="openTab(event, 'Paginas')"><?php esc_html_e('Pages', 'profiling-tool-for-wp'); ?></button>
      <button class="tablinks" onclick="openTab(event, 'Historial')"><?php esc_html_e('History', 'profiling-tool-for-wp'); ?></button>
      <button class="tablinks" onclick="openTab(event, 'Opciones')" id="OptionTab" ><?php esc_html_e('Options', 'profiling-tool-for-wp'); ?></button>
    </div>

    <!-- Tab content -->
    <div id="Opciones" class="tabcontent">
        <table class="form-table">
            <tbody><tr>
                <th scope="row"><?php esc_html_e('Plugin Language', 'profiling-tool-for-wp'); ?></th>
                <td>
                    <p id="p-lang">
                        <div id="langSelect">
                            <label style="margin-right: 10px;"><input type="radio"  name="langSelect" value="EN" id="engLang" style="margin-bottom: 5px" <?php echo $options["LANGUAGE"]=='EN'?'checked=""':''; ?> ><img src="<?php echo esc_url( PROFILING_TOOL_FOR_WP_URL ); ?>admin/img/us.jpg" width="26" /></label>
                            <label style="margin-right: 10px;"><input type="radio"  name="langSelect" value="ES" id="espLang" style="margin-bottom: 5px" <?php echo $options["LANGUAGE"]=='ES'?'checked=""':''; ?> ><img src="<?php echo esc_url( PROFILING_TOOL_FOR_WP_URL ); ?>admin/img/spain.jpg" width="26" /></label>
                            <label style="margin-right: 10px;"><input type="radio"  name="langSelect" value="GL" id="galLang" style="margin-bottom: 5px" <?php echo $options["LANGUAGE"]=='GL'?'checked=""':''; ?> ><img src="<?php echo esc_url( PROFILING_TOOL_FOR_WP_URL ); ?>admin/img/galicia.jpg" width="26" /></label>
                        </div>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Table sort order', 'profiling-tool-for-wp'); ?></th>
                <td>
                    <p><label><input type="radio"  name="tableOrd" value="asc" <?php echo $options["TABLE_SORT"]=='asc'?'checked=""':''; ?> id="table-ASC"> <?php esc_html_e('Upward', 'profiling-tool-for-wp'); ?></label></p>
                    <p><label><input type="radio" name="tableOrd" value="desc" <?php echo $options["TABLE_SORT"]=='desc'?'checked=""':''; ?> id="table-DESC"> <?php esc_html_e('Downward', 'profiling-tool-for-wp'); ?></label></p>
                </td>
            </tr>
        </tbody>
    </table>
    <div>
    <input type="button" class="button-primary" id="saveProfile" name="save-profile"  value="<?php esc_html_e('Save changes', 'profiling-tool-for-wp'); ?>">
</div>
    </div>

    <div id="Paginas" class="tabcontent">
    	<?php $pages = get_pages(); ?>

      <h3><?php esc_html_e('Pages', 'profiling-tool-for-wp'); ?></h3>
      <div class="postbox" style="display: block;">
            <div class="postbox-header">
                <h3><?php esc_html_e('Use of this section', 'profiling-tool-for-wp'); ?></h3>
            </div>
            <div class="inside">
                <div class="patp_multilingual-about">
                    <div class="patp_multilingual-about-info">
                        <div class="top-content">
                            <p class="plugin-description">
                                <?php esc_html_e('Press the blue botton bellow to measure the selected page', 'profiling-tool-for-wp'); ?>
                            </p>
                            <p class="plugin-description">
                                <?php esc_html_e('The input select bellow show all the site pages', 'profiling-tool-for-wp'); ?>
                            </p>
                            <p class="plugin-description">
                                <?php esc_html_e('Load times always vary because they depend on internet speed site cache active plugins among other things', 'profiling-tool-for-wp'); ?>
                            </p>
                            <p class="plugin-description">
                                <?php esc_html_e('Best for optimization is achieve lower times', 'profiling-tool-for-wp'); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<div class="postbox" style="display: block;">
			<div class="postbox-header" style="justify-content: left;">
                <h4 style="padding-right: 10px"><?php esc_html_e('Name of the Profile: ', 'profiling-tool-for-wp') ?></h4>
				<input class="regular-text" type="text" id="page_profile_name" name="name_page_profile" value="page-profile_<?php echo date("Ymd"); ?>">
            </div>
		</div>
        <div class="postbox no-bottom"><div class="postbox-header"><h3><?php esc_html_e('Select a page to measure performance', 'profiling-tool-for-wp'); ?><strong></strong></h3></div></div>
        <div class="lds-ellipsis hidden"></div>
        <div class="postbox" id="pageTest" style="padding: 15px;">
            <select name="page[]" id="pageSelected" multiple="multiple"> 
             <option value>Select a page</option>
                 <?php 
                  $pages = get_pages(); 
                  foreach ( $pages as $page ) { ?>
                    <option value="<?php echo esc_url( get_page_link( $page->ID ) ) ?>"><?php echo esc_html( $page->post_title ); ?></option>;

                <?php  } ?>
            </select>

            <button class="run_page_button" style="float: right;padding: 7px;background-color: #3276B1;width: 120px;border-radius: 15px;border: none;"><a style="color: white; text-decoration: none;" id="run_quick_page_tests"><?php esc_html_e('Start', 'profiling-tool-for-wp'); ?></a></button>
            
        </div>     
         
 	</div>

    <div id="Historial" class="tabcontent active">
		<h3><?php esc_html_e('Scan_history', 'profiling-tool-for-wp'); ?></h3>
		  <div>
			  <table id="historyTable" style="width:100%">
				  <thead>
					  <tr>
						  <th><?php esc_html_e('Profile', 'profiling-tool-for-wp'); ?></th>
						  <th><?php esc_html_e('Date', 'profiling-tool-for-wp'); ?></th>
						  <th><?php esc_html_e('Type', 'profiling-tool-for-wp'); ?></th>
						  <th><?php esc_html_e('Items', 'profiling-tool-for-wp'); ?></th>
						  <th><?php esc_html_e('Time', 'profiling-tool-for-wp'); ?></th>
						  <th><?php esc_html_e('Memory', 'profiling-tool-for-wp'); ?></th>
						  <th><?php esc_html_e('Queries db', 'profiling-tool-for-wp'); ?></th>
					  </tr>
				  </thead>
				  <tbody>
					 
				  </tbody>
			  </table>
		  </div>	
    </div>

    <div id="pluginProfile" class="patp-body tabcontent">
	
		<div class="postbox" style="display: block;">
            <div class="postbox-header">
                <h3><?php esc_html_e('Plugin use', 'profiling-tool-for-wp') ?></h3>
            </div>
            <div class="inside">
                <div class="patp_multilingual-about">
                    <div class="patp_multilingual-about-info">
                        <div class="top-content">
                            <p class="plugin-description">
                                <?php esc_html_e('Press the blue botton bellow to measure plugins', 'profiling-tool-for-wp'); ?>
                            </p>
                            <p class="plugin-description">
                                <?php esc_html_e('Table bellow show the current active plugins', 'profiling-tool-for-wp'); ?>
                            </p>
                            <p class="plugin-description">
                                <?php esc_html_e('You can run the test without any plugin or with all the active plugin with the links in the table', 'profiling-tool-for-wp'); ?>
                            </p>
                            <p class="plugin-description">
                                <?php esc_html_e('Every plugin have a link to run the test without that specific plugin', 'profiling-tool-for-wp'); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<div class="postbox" style="display: block;">
			<div class="postbox-header" style="justify-content: left;">
                <h3 style="padding-right: 10px"><?php esc_html_e('Name of the Profile: ', 'profiling-tool-for-wp') ?></h3>
				<input class="regular-text" type="text" id="profile_name" name="name_profile" value="profile_<?php echo date("Ymd"); ?>">
            </div>
		</div>
        <div class="patp_colors-container">
            <ul class="hidden">
                <li id="max_time"></li>
                <li id="max_db"></li>
                <li id="max_mem"></li>
            </ul>

            <div class="run_button"><a style="color: white; text-decoration: none;" id="run_quick_tests"><?php esc_html_e('Start', 'profiling-tool-for-wp'); ?></a></div>

            <div class="quick_test_report_wrap">
            <div id="quick_test_report">
            </div>
            </div>
            <hr>
            <div id="timeChart" style="background-color: white; padding: 20px; display: none; width: 100%; position: relative; height:70vh; width:80vw">
              <canvas id="myChart"></canvas>
            </div>

            <table class="wp-list-table widefat fixed striped table-view-list pages">
				<?php

				global $wpdb;

				$all_active_plugins = get_option('active_plugins');

				$themes = wp_get_themes(); // Obtiene la lista de temas instalados

				$lista_temas = array();
				foreach ($themes as $theme) {
					$nombre = $theme->get('Name');
					$version = $theme->get('Version');
					$lista_temas[] = ["name" => $nombre, "version" => $version];
				}

				if ( function_exists( 'get_plugins' ) ) {
					$existing_plugins = get_plugins();
				}

				//backup active plugins list
				if(count($all_active_plugins) > 1)update_option('ptfwp_backup_active_plugins', $all_active_plugins);
                $totalElements = (count($all_active_plugins) + count($lista_temas)) - 1;

				echo '<div class="lds-ellipsis hidden"></div>';
				
				echo '<div class="postbox no-bottom"><div class="postbox-header no-bottom"><h3>'. esc_html__('Current plugin theme active', 'profiling-tool-for-wp') .': <strong>'. $totalElements .'</strong></h3></div></div>';

				echo '<tr>';
				echo '<th>'.esc_html__('Plugin Name', 'profiling-tool-for-wp').'</th>';
				echo '<th>'.esc_html__('Run test', 'profiling-tool-for-wp').'</th>';
				echo '<th>'.esc_html__('Results from test', 'profiling-tool-for-wp').'</th>';
				echo '</tr>';

				echo '<tr id="plugin_all">';
				echo '<td><span class="plugin_name hidden">ALL</span><span class="nice_name">'. esc_html__('All activated', 'profiling-tool-for-wp') .'</span></td>';
				echo '<td><a class="patp_run_test" href="">'. esc_html__('Run test with all active plugins', 'profiling-tool-for-wp') .'</a></td>';
				echo '<td><span class="timing hidden"></span><span class="color hidden"></span><span class="result" id="results_all"></span></td>';
				echo '</tr>';

				echo '<tr id="plugin_no">';
				echo '<td><span class="plugin_name hidden">NONE</span><span class="nice_name">'. esc_html__('All deactivated', 'profiling-tool-for-wp') .'</span></td>';
				echo '<td><a class="patp_run_test" href="">'. esc_html__('Run test without any active plugin', 'profiling-tool-for-wp') .'</a></td>';
				echo '<td><span class="timing hidden"></span><span class="color hidden"></span><span class="result" id="results_no"></span></td>';
				echo '</tr>';

				sort($all_active_plugins);

				foreach($all_active_plugins as $key=>$plugin)
				{

					if(strpos($plugin, 'profiling-tool-for-wp') !== FALSE)
						continue;

					$plugin_name = $plugin;

					if(isset($existing_plugins[$plugin_name]))
						$plugin_name = $existing_plugins[$plugin_name]["Name"];

					echo '<tr id="plugin_'.esc_attr($key).'">';
					echo '<td><span class="plugin_name hidden">'.esc_html($plugin).'</span><span class="nice_name">'.esc_html($plugin_name).'</span></td>';
					echo '<td><a class="patp_run_test" href="">'. esc_html__('Run test in this plugin', 'profiling-tool-for-wp') .'</a></td>';
					echo '<td><span class="timing hidden"></span><span class="color hidden"></span><span class="result" id="results_'.esc_attr($key).'"></span></td>';
					echo '</tr>';

				}
					foreach ($lista_temas as $key=> $tema) {
						echo '<tr id="Theme_'.esc_attr($key).'">';
						echo '<td><span class="themes_name hidden">'.esc_html($tema['name']).'</span><span class="nice_name">'.esc_html($tema['name']).'</span></td>';
						echo '<td><a class="patp_run_test" href="">'. esc_html__('Run test in this Theme', 'profiling-tool-for-wp') .'</a></td>';
						echo '<td><span class="timing hidden"></span><span class="color hidden"></span><span class="result" id="results_'.esc_attr($key).'"></span></td>';
						echo '</tr>';
					}


				?>
							</table>
						</div>
	</div>
