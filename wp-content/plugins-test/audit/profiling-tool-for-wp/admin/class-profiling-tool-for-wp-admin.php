<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://dnsempresas.com/
 * @since      1.0.0
 *
 * @package    Profiling_Tool_For_Wp
 * @subpackage Profiling_Tool_For_Wp/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Profiling_Tool_For_Wp
 * @subpackage Profiling_Tool_For_Wp/admin
 * @author     Dns Empresas <Administracion@dnsempresas.com>
 */
class Profiling_Tool_For_Wp_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Profiling_Tool_For_Wp_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Profiling_Tool_For_Wp_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/profiling-tool-for-wp-admin.css', array(), $this->version, 'all' );

		wp_register_style( 'select2-styles', plugin_dir_url( __FILE__ ) . 'css/select2.min.css', array(), '4.1.0', 'all' );
		
		wp_enqueue_style( 'select2-styles' );
		
		wp_register_style( 'dataTable-styles', plugin_dir_url( __FILE__ ) . 'css/datatables.min.css', array(), '2.1.7', 'all' );
		
		wp_enqueue_style( 'dataTable-styles' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Profiling_Tool_For_Wp_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Profiling_Tool_For_Wp_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		$screen = get_current_screen();

		if ( isset( $_GET['page'] ) && $_GET['page'] == 'profiling_tool_for_wp' || ( $screen->id === 'plugins' ) ) {

			$params = array (
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'ajax_nonce' => wp_create_nonce( 'ptfwp_add_security_form_nonce' ),
				'tab_exists' => isset($_GET['tab']) ? true : false,
				'homeUrl' => PROFILING_TOOL_FOR_WP_MAIN_URL,
				'lang' => get_option( "PROFILING_TOOL_FOR_WP_LANGUAGE" )
			);
			
			wp_register_script( 'dataTables', plugin_dir_url( __FILE__ ) . 'js/datatables.min.js', array(), '2.1.7', true );
			
			wp_script_add_data('dataTables', 'defer', true);

			wp_enqueue_script( 'dataTables' );
		
			wp_register_script( 'select2-script', plugin_dir_url( __FILE__ ) . 'js/select2.full.min.js', array(), '4.1.0', true );
			
			wp_enqueue_script( 'select2-script' );

			wp_register_script( 'chartjs-script', plugin_dir_url( __FILE__ ) . 'js/chart.js', array(), '4.4.4', true );

			wp_enqueue_script( 'chartjs-script' );

			wp_register_script( 'chart-umd-js-script', plugin_dir_url( __FILE__ ) . 'js/chart.umd.js', array(), '4.4.4',  true );

			wp_enqueue_script( 'chart-umd-js-script' );

			wp_enqueue_script( 'ptfwp_ajax_handle', plugin_dir_url( __FILE__ ) . 'js/profiling-tool-for-wp-admin.js', array( 'jquery' ), $this->version, true );

			wp_script_add_data('ptfwp_ajax_handle', 'defer', true);
					
			wp_localize_script( 'ptfwp_ajax_handle', 'wp_ajax', $params );

		}

	}


	/**
	 * Admin Page Display
	 * 
	 * @since    1.0.0
	 */
	public function admin_page_display_index() {
	
		include_once( 'partials/' . $this->plugin_name . '-admin-display.php' );

	}

    /**
     * To add Plugin Menu and Settings page
     * 
     * @since    1.0.0
     */
    public function plugin_menu() {

        ob_start();

        add_menu_page(  'Profiling Tool For WP',
        				'Profiling Tool For WP',
        				'manage_options',
        				'profiling_tool_for_wp',
        				array($this, 'admin_page_display_index'),
        				'dashicons-superhero' );
	
    }


    /**
	 * Add the setting link to the wordpress list plugins.
	 *
	 * @since    1.0.0
	 */
	public function ptfwp_settings_link( $links ) {

		$settings_link = array( '<a style="color:green;font-weight:bold;" href="'.esc_url( get_admin_url().'admin.php?page=profiling_tool_for_wp&tab=options' ).'">' . __( 'Ajustes', 'profiling-tool-for-wp' ) . '</a>', );

		return array_merge(  $settings_link, $links );

	}


	/**
	 * Function to save the options of the plugin.
	 *
	 * @since    1.0.0
	 */
	public function save_plugin_options() {
		
		if ( isset($_REQUEST) ) {
			
			if ( ! check_ajax_referer( 'ptfwp_add_security_form_nonce', 'security', false ) ) {
				wp_send_json_error( 'Invalid security token sent.' );
				die();
			}
					
			global $wpdb;

			$tabla = $wpdb->prefix . 'ptfwp_options';

			$fila = array(
				  'LANGUAGE' => sanitize_text_field( $_REQUEST['language'] ),
				  'TABLE_SORT' => sanitize_text_field( $_REQUEST['table_ord'] ),
			 			);
			
			$format = array( '%s', '%s' );

			$where = array(
					'ID' => '1',
					);
			
			$where_format = array(
						'%d'
					);
			
			return $wpdb->update( $tabla, $fila, $where, $format, $where_format );
			
			die();
				
		}
		
	}


	/**
	 * Function to reload the history table when the main section is access.
	 *
	 * @since    1.0.0
	 */
	public function load_table_data() {
		
		if ( ! check_ajax_referer( 'ptfwp_add_security_form_nonce', 'security', false ) ) {
				wp_send_json_error( 'Invalid security token sent.' );
				die();
			}
		
		global $wpdb;

		// Nombre de la tabla
		$table_name = $wpdb->prefix . 'ptfwp_history';

		// Realiza la consulta para obtener los datos
		$query = "SELECT * FROM $table_name";
		
		$results = $wpdb->get_results( $query );

		// Retorna los datos en formato JSON
		wp_send_json($results);
		
		die();
		
	}


	/**
	 * Function to save the page profile in the history table.
	 *
	 * @since    1.0.0
	 */
	public function save_page_profile(){
		
		if ( isset($_REQUEST) ) {
			
			if ( ! check_ajax_referer( 'ptfwp_add_security_form_nonce', 'security', false ) ) {
				wp_send_json_error( 'Invalid security token sent.' );
				die();
			}
			
			$nombre = sanitize_text_field( $_REQUEST['nombre'] );
			$fecha = sanitize_text_field( $_REQUEST['fecha'] );
			$tipo = sanitize_text_field( $_REQUEST['tipo'] );
			$objetos = sanitize_text_field( $_REQUEST['items'] );
			$tiempo = sanitize_text_field( $_REQUEST['time'] );

			$maxTime = $tiempo / 1000;

			global $wpdb;
			$nombre_tabla = $wpdb->prefix . 'ptfwp_history';

			$fila = array(
				  'nombre'=>$nombre,
				  'fecha'=>$fecha,
				  'tipo'=>$tipo,
				  'items'=>$objetos,
				  'tiempo'=>$maxTime,
				 );
			
			$format = array( '%s','%s', '%s', '%s', '%s' );

			$wpdb->insert( $nombre_tabla, $fila, $format );
		
		}
		
		die();
		
	}


	/**
	 * Function to save the main results in the history table.
	 *
	 * @since    1.0.0
	 */
	public function save_profile(){
		
		if ( isset($_REQUEST) ) {
			
			if ( ! check_ajax_referer( 'ptfwp_add_security_form_nonce', 'security', false ) ) {
				wp_send_json_error( 'Invalid security token sent.' );
				die();
			}
			
			$nombre = sanitize_text_field( $_POST['name'] );
			$fecha = sanitize_text_field( $_POST['fecha'] );
			$tipo = sanitize_text_field( $_POST['tipo'] );
			$objetos = sanitize_text_field( $_POST['items'] );
			$db = sanitize_text_field( $_POST['sql'] );
			$memory = sanitize_text_field( $_POST['memory'] );
			$time = sanitize_text_field( $_POST['time'] );
			$info = sanitize_text_field( $_POST['info'] );

			$maxTime = $time / 1000;

			global $wpdb;
			$nombre_tabla = $wpdb->prefix . 'ptfwp_history';

			$fila = array(
				  'nombre' => $nombre,
				  'fecha' => $fecha,
				  'tipo' => $tipo,
				  'items' => $objetos,
				  'tiempo' => $maxTime,
				  'memory' => $memory,
				  'queries' => $db,
				 );
			
			$format = array( '%s','%s', '%s', '%s', '%s', '%s', '%s' );
			
			$wpdb->insert( $nombre_tabla, $fila, $format );
			
			return $wpdb->insert_id;
			
		}
		
	}
	
	/**
	 * Function to show modal on deactivation plugin button.
	 *
	 * @since    1.1.2
	 */
	public function ptfwp_add_deactivation_modal(){
		
		?>
		<div id="plugin-deactivation-modal" class="plugin-modal-overlay">
        <div class="plugin-modal">
            <h2>Esta por desactivar la herramienta de profiling para Wordpress</h2>
            <p>Por favor ayudenos compartiendo su experiencia en caso de que desee desinstalar.</p>
            <div class="modal-buttons">
                <button id="go-to-form" class="form-button">Ir al formulario</button>
                <button id="confirm-deactivate" class="confirm-button">Desactivar</button>
                <button class="cancel-button">Cancelar</button>
            </div>
        </div>
    </div>
	   <?php
		
	}

}
