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
    <h1 class="wp-heading-inline">Medir o rendemento da páxina</h1>

    <br /><br />

    <!-- Tab links -->
    <div class="tab">
      <button class="tablinks" onclick="openTab(event, 'pluginProfile')" id="defaultOpen">Plugins e temas</button>  
      <button class="tablinks" onclick="openTab(event, 'Paginas')">Páxinas</button>
      <button class="tablinks" onclick="openTab(event, 'Historial')">Historial</button>
      <button class="tablinks" onclick="openTab(event, 'Opciones')" id="OptionTab" >Opciones</button>
    </div>

    <!-- Tab content -->
    <div id="Opciones" class="tabcontent">
        <table class="form-table">
            <tbody><tr>
                <th scope="row">Idioma do plugin</th>
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
                <th scope="row">Orden de ordenación da táboa</th>
                <td>
                    <p><label><input type="radio"  name="tableOrd" value="asc" <?php echo $options["TABLE_SORT"]=='asc'?'checked=""':''; ?> id="table-ASC"> Arriba</label></p>
                    <p><label><input type="radio" name="tableOrd" value="desc" <?php echo $options["TABLE_SORT"]=='desc'?'checked=""':''; ?> id="table-DESC"> Abaixo</label></p>
                </td>
            </tr>
        </tbody>
    </table>
    <div>
    <input type="button" class="button-primary" id="saveProfile" name="save-profile"  value="Gardar os cambios">
</div>
    </div>

    <div id="Paginas" class="tabcontent">
    	<?php $pages = get_pages(); ?>

      <h3>Páxinas</h3>
      <div class="postbox" style="display: block;">
            <div class="postbox-header">
                <h3>'Uso desta sección</h3>
            </div>
            <div class="inside">
                <div class="patp_multilingual-about">
                    <div class="patp_multilingual-about-info">
                        <div class="top-content">
                            <p class="plugin-description">
                                Preme o botón azul de abaixo para medir a páxina seleccionada
                            </p>
                            <p class="plugin-description">
                                O campo de selección de abaixo mostra todas as páxinas do sitio
                            </p>
                            <p class="plugin-description">
                                'Os tempos de carga sempre varían porque dependen da velocidade de Internet, da caché do sitio, dos complementos activos, entre outras cousas
                            </p>
                            <p class="plugin-description">
                                O mellor para a optimización é conseguir tempos máis baixos
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<div class="postbox" style="display: block;">
			<div class="postbox-header" style="justify-content: left;">
                <h4 style="padding-right: 10px">Nome para a proba: </h4>
				<input class="regular-text" type="text" id="page_profile_name" name="name_page_profile" value="profile_<?php echo date("Ymd"); ?>">
            </div>
		</div>
        <div class="postbox no-bottom"><div class="postbox-header"><h3>Seleccione unha páxina para medir o rendemento<strong></strong></h3></div></div>
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

            <button class="run_page_button" style="float: right;padding: 7px;background-color: #3276B1;width: 120px;border-radius: 15px;border: none;"><a style="color: white; text-decoration: none;" id="run_quick_page_tests">Comezar</a></button>
            
        </div>     
         
 	</div>

    <div id="Historial" class="tabcontent active">
		<h3>Historial de exploración</h3>
		  <div>
			  <table id="historyTable" style="width:100%">
				  <thead>
					  <tr>
						  <th>Perfil</th>
						  <th>Hora</th>
						  <th>Tipo</th>
						  <th>Elementos</th>
						  <th>Tiempo</th>
						  <th>Memoria</th>
						  <th>Consultas de base de datos</th>
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
                <h3>Uso do plugin</h3>
            </div>
            <div class="inside">
                <div class="patp_multilingual-about">
                    <div class="patp_multilingual-about-info">
                        <div class="top-content">
                            <p class="plugin-description">
                                Preme o botón azul de abaixo para medir os complementos
                            </p>
                            <p class="plugin-description">
                                A seguinte táboa mostra os complementos activos actuais
                            </p>
                            <p class="plugin-description">
                                Podes executar a proba sen ningún complemento ou con todos os complementos activos coas ligazóns da táboa
                            </p>
                            <p class="plugin-description">
                                Todos os complementos teñen unha ligazón para executar a proba sen ese complemento específico
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<div class="postbox" style="display: block;">
			<div class="postbox-header" style="justify-content: left;">
                <h3 style="padding-right: 10px">Nome para a proba: </h3>
				<input class="regular-text" type="text" id="profile_name" name="name_profile" value="profile_<?php echo date("Ymd"); ?>">
            </div>
		</div>
        <div class="patp_colors-container">
            <ul class="hidden">
                <li id="max_time"></li>
                <li id="max_db"></li>
                <li id="max_mem"></li>
            </ul>

            <div class="run_button"><a style="color: white; text-decoration: none;" id="run_quick_tests">Comezar</a></div>

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
				
				echo '<div class="postbox no-bottom"><div class="postbox-header no-bottom"><h3>Plugins e temas activos actualmente: <strong>'. $totalElements .'</strong></h3></div></div>';

				echo '<tr>';
				echo '<th> Nome do plugin </th>';
				echo '<th>Executar proba</th>';
				echo '<th> Resultados da proba </th>';
				echo '</tr>';

				echo '<tr id="plugin_all">';
				echo '<td><span class="plugin_name hidden">ALL</span><span class="nice_name">Todo activado</span></td>';
				echo '<td><a class="patp_run_test" href="">Executa a proba con todos os plugins activos</a></td>';
				echo '<td><span class="timing hidden"></span><span class="color hidden"></span><span class="result" id="results_all"></span></td>';
				echo '</tr>';

				echo '<tr id="plugin_no">';
				echo '<td><span class="plugin_name hidden">NONE</span><span class="nice_name">Todo desactivado</span></td>';
				echo '<td><a class="patp_run_test" href="">Executa a proba sen ningún plugin activo</a></td>';
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
					echo '<td><a class="patp_run_test" href="">Executa a proba só neste plugin</a></td>';
					echo '<td><span class="timing hidden"></span><span class="color hidden"></span><span class="result" id="results_'.esc_attr($key).'"></span></td>';
					echo '</tr>';

				}
					foreach ($lista_temas as $key=> $tema) {
						echo '<tr id="Theme_'.esc_attr($key).'">';
						echo '<td><span class="themes_name hidden">'.esc_html($tema['name']).'</span><span class="nice_name">'.esc_html($tema['name']).'</span></td>';
						echo '<td><a class="patp_run_test" href="">Executa a proba só neste tema</a></td>';
						echo '<td><span class="timing hidden"></span><span class="color hidden"></span><span class="result" id="results_'.esc_attr($key).'"></span></td>';
						echo '</tr>';
					}


				?>
							</table>
						</div>
	</div>