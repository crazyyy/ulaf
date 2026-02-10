<?php
/** 
 * 
 * Plugin Name: Codecorun - Other Installation Details
 * Plugin URI: https://codecorun.com/plugins/other-installation-details/
 * Description: A simple plugin to display other installation details of the plugin in your website such as installation date and the author
 * Author:      Codecorun
 * Plugin Type: information
 * Author URI: https://codecorun.com
 * Version: 1.0.0
 * Text Domain: codecorun-other-installation-details
 * 
 * 
*/

defined( 'ABSPATH' ) or die( 'No access area' );
define('CODECORUN_OID_PATH', plugin_dir_path( __FILE__ ));
define('CODECORUN_OID_URL', plugin_dir_url( __FILE__ ));
define('CODECORUN_OID_PREFIX','codecorun_oid');
define('CODECORUN_OID_VERSION','1.0.0');

add_action( 'init', 'codecorun_oid_load_textdomain' );
function codecorun_oid_load_textdomain() {
	load_plugin_textdomain( 'codecorun-other-installation-details', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}

function codecorun_oid_install(){
    codecorun_oid_register_plugin( 'codecorun-other-installation-details/codecorun-oid-plugin.php' );
}
register_activation_hook( __FILE__, 'codecorun_oid_install' );

//autoload classes
spl_autoload_register(function ($class) {

	if(strpos($class,CODECORUN_OID_PREFIX) !== false){
		$class = preg_replace('/\\\\/', '{'.CODECORUN_OID_PREFIX.'}', $class);
        $fullclass = $class;
		$class = explode('{'.CODECORUN_OID_PREFIX.'}', $class);
		if(!empty(end($class))){
			$filename = str_replace("_", "-", end($class));
            $admin = (strpos($fullclass,'admin') !== false)? 'admin/' : null;
			include $admin.'includes/'.$filename.'.php';
		}
	}

});


add_action('plugins_loaded','codecorun_oid_init');
function codecorun_oid_init(){
	
	if(current_user_can('administrator')){
		//load admin class
		new codecorun\oid\admin\codecorun_oid_admin_class();
	}
	
}

if( !function_exists('codecorun_oid_register_plugin') ){

    function  codecorun_oid_register_plugin( $plugin = null, $type = 'activate' )
    {

        if( !$plugin )
            return;

        $code_oid = get_option('codecorun_oid_details');

        $it_has = false;

        $data = [
            'plugin_name' => $plugin,
            'plugin_installation_date' => current_datetime()->format('Y-m-d H:i:s'),
            'status' => 'active',
            'author' => get_current_user_id()
        ];

        if( $type == 'deactivate' ){
            $data[ 'plugin_deactivated_date' ] = current_datetime()->format('Y-m-d H:i:s');
        }

        if( $code_oid ){
            
            foreach( $code_oid as $index => $oid ){
                if( $oid[ 'plugin_name' ] == $plugin){
                    $it_has = true;
                    if( $type == 'deactivate' ){
                        $oid[ 'plugin_deactivated_date' ] = current_datetime()->format('Y-m-d H:i:s');
                    }else{
                        $oid[ 'plugin_reactivated_date' ] = current_datetime()->format('Y-m-d H:i:s');
                    }
                    $oid[ 'author' ] = get_current_user_id();
                    $code_oid[ $index ] = $oid;
                    break;
                }
            }
           if( !$it_has ){
                $code_oid[] = $data;
                update_option( 'codecorun_oid_details', $code_oid );    
           }else{
                update_option( 'codecorun_oid_details', $code_oid );
           }
            

        }else{
            update_option( 'codecorun_oid_details', [ $data ] );
        }
    }

}
?>