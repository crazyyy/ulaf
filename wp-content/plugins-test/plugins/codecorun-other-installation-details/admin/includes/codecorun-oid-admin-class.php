<?php
/**
 * 
 * wcdr-admin-class
 * @version 1.0
 * 
 */
namespace codecorun\oid\admin;

defined( 'ABSPATH' ) or die( 'No access area' );

class codecorun_oid_admin_class
{

    public function __construct()
    {

        add_filter( 'manage_plugins_columns', [$this, 'column_header'] );
        add_action( 'manage_plugins_custom_column', [$this, 'column_value'], 10, 2 );
        add_action( 'activated_plugin', [$this,'reg_activated_plugin'], 10, 2 );
        add_action( 'deactivated_plugin', [$this,'reg_deactivated_plugin'], 10, 2 );
        
    }

    /**
     * 
     * column_header
     * @since 1.0.0
     * @param array
     * @return array
     * 
     * 
     */
    public function column_header( $columns ) {
        $columns['codecorun_oid_date_installed'] = __('Date Installed', 'codecorun-plugin-installation-details');
        $columns['codecorun_oid_date_reactivated'] = __('Date Reactivated', 'codecorun-plugin-installation-details');
        $columns['codecorun_oid_date_deactivated'] = __('Date Deactivated', 'codecorun-plugin-installation-details');
        $columns['codecorun_oid_author'] = __('Author', 'codecorun-plugin-installation-details');
        return $columns;
    }

    /**
     * 
     * 
     * column_value
     * @since 1.0.0
     * @param string - column name
     * @param string - plugin file path
     * @return 
     * 
     * 
     */
    public function column_value( $column_name, $plugin_file ) {
        
        $oids = get_option( 'codecorun_oid_details' );

        $date_format = get_option('date_format');
        $time_format = get_option('time_format');
  

        if ( 'codecorun_oid_date_installed' === $column_name ) {

            if( $oids ){
                foreach( $oids as $oid ){
                    if( $oid[ 'plugin_name' ] == $plugin_file ){
                        echo date( $date_format, strtotime($oid[ 'plugin_installation_date' ]) ) .' - '. date( $time_format, strtotime($oid[ 'plugin_installation_date' ]) );
                    }
                }
            }

        }
        if( 'codecorun_oid_author' == $column_name ){
            if( $oids ){
                foreach( $oids as $oid ){
                    if( $oid[ 'plugin_name' ] == $plugin_file ){
                        $author = $oid[ 'author' ];
                        $user = get_userdata( $author );
                        echo '<a href="user-edit.php?user_id='.esc_attr( $author ).'" target="_blank">'.esc_html( $user->user_login ).'</a>';
                    }
                }
            }
            
        }

        if( 'codecorun_oid_date_reactivated' == $column_name ){
            if( $oids ){
                foreach( $oids as $oid ){
                    if( $oid[ 'plugin_name' ] == $plugin_file ){
                        if( isset($oid[ 'plugin_reactivated_date' ]) ){
                            echo date( $date_format, strtotime($oid[ 'plugin_reactivated_date' ]) ) .' - '. date( $time_format, strtotime($oid[ 'plugin_reactivated_date' ]) );
                        }
                    }
                }
            }
            
        }

        if( 'codecorun_oid_date_deactivated' == $column_name ){
            if( $oids ){
                foreach( $oids as $oid ){
                    if( $oid[ 'plugin_name' ] == $plugin_file ){
                        if( isset($oid[ 'plugin_deactivated_date' ]) ){
                            echo date( $date_format, strtotime($oid[ 'plugin_deactivated_date' ]) ) .' - '. date( $time_format, strtotime($oid[ 'plugin_deactivated_date' ]) );
                        }
                    }
                }
            }
        }
    }

    /**
     * 
     * reg_activated_plugin
     * @since 1.0.0
     * @param string - plugin file path
     * @param unknown
     * @return 
     * 
     * 
     */
    public function reg_activated_plugin( $plugin, $network_activation ) {
        // do stuff
        codecorun_oid_register_plugin( $plugin );
    }

    /**
     * 
     * reg_deactivated_plugin
     * @since 1.0.0
     * @param string
     * @param unknown
     * @return
     * 
     * 
     */

    public function reg_deactivated_plugin( $plugin, $network_activation )
    {
        codecorun_oid_register_plugin( $plugin, 'deactivate' );
    }
}
?>