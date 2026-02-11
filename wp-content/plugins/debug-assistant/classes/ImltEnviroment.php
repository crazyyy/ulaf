<?php
class ImltEnviroment
{
  public function sistem_report_details() {
    $wordpress_data = '';
      if(is_multisite())  {
        $imlt_multisite =  'Multisite - Yes';
      } else {
        $imlt_multisite = 'Multisite - No';
      }

      // wordpress data
     $imlt_siteUrl = 'SITE_URL - ' . site_url();
     $imlt_homeUrl = 'HOME URL - ' . home_url();
     $imlt_wp_version = 'Wordpress version - ' . get_bloginfo( 'version' );
     $imlt_permalink = 'Permalink - ' . get_option('permalink_structure');
     $imlt_current_theme = 'Current theme - ' .  wp_get_theme()->Name . '(' .wp_get_theme()->Version .')';
     $imlt_postTypes = 'Post types - ' . implode( ', ', get_post_types( '', 'names' ) );
     $imlt_totalUsers = 'Total users - ' . count_users()['total_users'] . "\n" ;

     $wordpress_data .= "<ul class='list-group'>";
     $wordpres_data_table_li = "<li class='list-group-item'>";
     $wordpress_data .= $wordpres_data_table_li . $imlt_multisite . "</li>" . "\n";
     $wordpress_data .= $wordpres_data_table_li . $imlt_siteUrl . "</li>". "\n";
     $wordpress_data .= $wordpres_data_table_li . $imlt_homeUrl . "</li>". "\n";
     $wordpress_data .= $wordpres_data_table_li . $imlt_wp_version . "</li>". "\n";
     $wordpress_data .= $wordpres_data_table_li . $imlt_permalink . "</li>". "\n";
     $wordpress_data .= $wordpres_data_table_li . $imlt_current_theme . "</li>". "\n";
     $wordpress_data .= $wordpres_data_table_li . $imlt_postTypes . "</li>". "\n";
     $wordpress_data .= $wordpres_data_table_li . $imlt_totalUsers . "</li>". "\n";
     $wordpress_data .= "</ul>";
     $wordpress_data .= "<div class='imlt-alert-info alert alert-info' role='alert'><b>Wordpress Config</b></div>". "\n". "\n";

    // wordpress config
      $wpdbg = (defined( 'WP_DEBUG' ) && WP_DEBUG == true) ? ( 'Enabled') : ( 'Disabled');
    $imlt_debugwp =   'WP_DEBUG - ' . $wpdbg;
    $imlt_memory_limit =  'WP memory limit - ' . ini_get( 'memory_limit' );
    global $wpdb;
    $imlt_db_prefix = 'Wordpress database prefix - ' . $wpdb->base_prefix;
    $imlt_show_on_front =  'Show on front - ' . get_option('show_on_front ');
    $imlt_front_page =  'Page on front - ' . get_the_title(get_option('page_on_front')) . '(#' . get_option('page_on_front') .')';
    $imlt_blog_page =  'Posts page - ' . get_the_title(get_option( 'page_for_posts' )) . '(#' . get_option('page_for_posts') .')';

    $wordpress_data .= "<ul class='list-group'>";
    $wordpress_data .= $wordpres_data_table_li . $imlt_debugwp . "</li>". "\n";
    $wordpress_data .= $wordpres_data_table_li . $imlt_memory_limit . "</li>". "\n";
    $wordpress_data .= $wordpres_data_table_li . $imlt_db_prefix . "</li>". "\n";
    $wordpress_data .= $wordpres_data_table_li . $imlt_show_on_front . "</li>". "\n";
    $wordpress_data .= $wordpres_data_table_li . $imlt_front_page . "</li>". "\n";
    $wordpress_data .= $wordpres_data_table_li . $imlt_blog_page . "</li>". "\n". "\n";
    $wordpress_data .= "</ul>";
    $wordpress_data .= "<div class='imlt-alert-info alert alert-info' role='alert'><b>Server Data</b></div>". "\n". "\n";

    $imlt_jQuery_vs = 'jQuery version - ' . $GLOBALS['wp_scripts']->registered['jquery']->ver;
    $imlt_php_vs = 'PHP version - ' . PHP_VERSION;
    $imlt_MySQL_vs = 'MySQL version - ' . $wpdb->db_version();
    $imlt_server_sftware = 'Server software version - ' . $_SERVER['SERVER_SOFTWARE'];

    $wordpress_data .= "<ul class='list-group'>";
    $wordpress_data .= $wordpres_data_table_li . $imlt_jQuery_vs . "<br>". "\n";
    $wordpress_data .= $wordpres_data_table_li . $imlt_php_vs . "<br>". "\n";
    $wordpress_data .= $wordpres_data_table_li . $imlt_MySQL_vs . "<br>". "\n";
    $wordpress_data .= $wordpres_data_table_li . $imlt_server_sftware . "<br>". "\n". "\n";
    $wordpress_data .= "</ul>";
    $wordpress_data .= "<div class='imlt-alert-info alert alert-info' role='alert'><b>PHP Configuration</b></div>". "\n". "\n";
    $imlt_safe_mode_check = ini_get( 'safe_mode' ) ? 'yes' : 'no';
    $imlt_safe_mode = 'Safe mode - ' . $imlt_safe_mode_check;
    $imlt_memory_limit = 'Memory limit - '. ini_get( 'memory_limit' );
    $imlt_upload_max_filezise = 'Upload max filesize - ' . ini_get( 'upload_max_filesize' );
    $imlt_post_max_size = 'Post max size - ' . ini_get('post_max_size');
    $imlt_time_limit = 'Time limit - ' . ini_get( 'max_execution_time' );
    $imlt_max_input_vars = 'Max input vars - ' . ini_get( 'max_input_vars' );
    $imlt_display_errors_check = ini_get( 'display_errors' ) == 0 ? 'off' : 'on';
    $imlt_display_errors = 'Display errors - ' . $imlt_display_errors_check;
    $imlt_sessions_check = PHP_SESSION_DISABLED != session_status()  ? 'enabled' : 'disabled';
    $imlt_sessions = 'Sessions - ' . $imlt_sessions_check;
    $imlt_session_name = 'Session name - ' . esc_html( ini_get( 'session.name' ) );
    $imlt_cookies_check = ini_get( 'session.use_cookies' ) ? 'on' : 'off';
    $imlt_cookies = 'Cookies - ' . $imlt_cookies_check;
    $imlt_cookie_path = 'Cookie path - ' .  esc_html( ini_get( 'session.cookie_path' ) );
    $imlt_save_path = 'Save path - ' . esc_html( ini_get( 'session.save_path' ) );
    $imlt_fsockopen_check = function_exists( 'fsockopen' ) ? 'on' : 'off';
    $imlt_fsockopen = 'FSOCKOPEN - ' . $imlt_fsockopen_check;
    $imlt_cURL_check = function_exists( 'curl_init' ) ? 'on' : 'off';
    $imlt_cURL = 'cURL - ' . $imlt_cURL_check;
    $imlt_soap_client_check = class_exists( 'SoapClient' ) ? 'yes' : 'no';
    $imlt_soap_client = 'SOAP client - ' . $imlt_soap_client_check;
      $check_suhosin = extension_loaded( 'suhosin' ) ? 'yes' : 'no';
    $imlt_suhosin = 'SUHOSIN - ' . $check_suhosin;
      $check_openSSL = extension_loaded('openssl') ? 'yes' : 'no';
    $imlt_openSSL = 'OpenSSL - ' . $check_openSSL;

    $wordpress_data .= "<ul class='list-group'>";
    $wordpress_data .= $wordpres_data_table_li . $imlt_safe_mode . "<br>". "\n";
    $wordpress_data .= $wordpres_data_table_li . $imlt_memory_limit . "<br>". "\n";
    $wordpress_data .= $wordpres_data_table_li . $imlt_upload_max_filezise . "<br>". "\n";
    $wordpress_data .= $wordpres_data_table_li . $imlt_post_max_size . "<br>". "\n";
    $wordpress_data .= $wordpres_data_table_li . $imlt_time_limit . "<br>". "\n";
    $wordpress_data .= $wordpres_data_table_li . $imlt_max_input_vars . "<br>". "\n";
    $wordpress_data .= $wordpres_data_table_li . $imlt_display_errors . "<br>". "\n";
    $wordpress_data .= $wordpres_data_table_li . $imlt_sessions . "<br>". "\n";
    $wordpress_data .= $wordpres_data_table_li . $imlt_session_name . "<br>". "\n";
    $wordpress_data .= $wordpres_data_table_li . $imlt_cookies . "<br>". "\n";
    $wordpress_data .= $wordpres_data_table_li . $imlt_cookie_path . "<br>". "\n";
    $wordpress_data .= $wordpres_data_table_li . $imlt_save_path . "<br>". "\n";
    $wordpress_data .= $wordpres_data_table_li . $imlt_fsockopen . "<br>". "\n";
    $wordpress_data .= $wordpres_data_table_li . $imlt_cURL . "<br>". "\n";
    $wordpress_data .= $wordpres_data_table_li . $imlt_soap_client . "<br>". "\n";
    $wordpress_data .= $wordpres_data_table_li . $imlt_suhosin . "<br>". "\n";
    $wordpress_data .= $wordpres_data_table_li . $imlt_openSSL . "<br>". "\n". "\n";
    $wordpress_data .= "</ul>";
    $wordpress_data .= "<div class='imlt-alert-info alert alert-info' role='alert'><b>Plugin Infos</b></div>". "\n" . "\n";

      $imlt_plugins	= get_plugins();
      $imlt_active_plugins		= get_option( 'active_plugins', array() );


      		// output active plugins
      		if ( $imlt_plugins ) :
      			$imlt_active_plg_list	= "<div id='accordion_active' role='tablist'><a class='imlt-tgl-clps' data-toggle='collapse' href='#collapseOne' aria-expanded='false' aria-controls='collapseOne'><div class='alert alert-success' role='alert'><b>Active Plugins</b>: (".count( $imlt_active_plugins ).")</div></a>". "\n". "\n";
            $imlt_active_plg_list .= "<div class='collapse' id='collapseOne' role='tabpanel'  data-parent='#accordion_active'><ol class='list-group'>";
      			foreach ( $imlt_plugins as $plugin_path => $plugin ) :
      				if ( ! in_array( $plugin_path, $imlt_active_plugins ) )
      					continue;
      				$imlt_active_plg_list	.= "<li class='list-group-item'>" .$plugin['Name'] . " " . $plugin['Version'] ."</li>". "\n";
      			endforeach;

      		endif;
          $imlt_active_plg_list .= "</ol></div>";

      		// output inactive plugins
      		if ( $imlt_plugins ) :
      			$imlt_inactive_plg_list	= "<div id='accordion_inactive' role='tablist'><a class='imlt-tgl-clps' data-toggle='collapse' href='#collapseTwo' aria-expanded='false' aria-controls='collapseTwo'><div class='alert alert-warning' role='alert'><b>Inactive Plugins</b>: (".( count( $imlt_plugins ) - count( $imlt_active_plugins ) ).")</div></a>". "\n". "\n";
            $imlt_inactive_plg_list .= "<div class='collapse' id='collapseTwo' role='tabpanel'  data-parent='#accordion_inactive'><ol>";
      			foreach ( $imlt_plugins as $plugin_path => $plugin ) :
      				if (  in_array( $plugin_path, $imlt_active_plugins ) )
      					continue;
      				$imlt_inactive_plg_list	.= "<li class='list-group-item'>" .$plugin['Name'] . " " . $plugin['Version'] ."</li>". "\n";
      			endforeach;
      			$imlt_inactive_plg_list	.= "\n";
      		endif;
          $imlt_inactive_plg_list .= "</ol></div></div>";


      $wordpress_data .= $imlt_active_plg_list. "\n";
      $wordpress_data .= $imlt_inactive_plg_list. "\n";

     return $wordpress_data. "\n";


  }


  public function imlt_display_plugins($imlt_plg) {

    $imlt_plugins	= get_plugins();
    $imlt_active_plugins = get_option( 'active_plugins', array() );

      if ($imlt_plg == 'active') {

        $imlt_active_plg_list = "<ol class='list-group'>";
        foreach($imlt_plugins as $imlt_active_plugins_key => $imlt_active_plugins_value) {

          if(in_array($imlt_active_plugins_key, $imlt_active_plugins)) :
          $imlt_active_plg_list .=  "<li class='list-group-item'>" . $imlt_active_plugins_value['Name'] . "</li>";
          endif;
        }

        $imlt_active_plg_list .= "</ol>";
        return $imlt_active_plg_list;
      }

      if($imlt_plg == 'inactive') {

        $imlt_inactive_plug_list = "<ol class='list-group'>";

        foreach ( $imlt_plugins as $imlt_inactive_plugins_key => $imlt_inactive_plugins_value ) {

          if( !in_array($imlt_inactive_plugins_key, $imlt_active_plugins )) :
          $imlt_inactive_plug_list .= "<li class='list-group-item'>" . $imlt_inactive_plugins_value['Name'] . "</li>";
          endif;

      }
        $imlt_inactive_plug_list .= "</ol>";
        return $imlt_inactive_plug_list;
      }

  }


  public function imlt_phpinfo_details() {
    ob_start();
    phpinfo();
    $phpinfo_data = ob_get_contents();
    ob_end_clean();
    $phpinfo_data = preg_replace('%^.*<body>(.*)</body>.*$%ms','$1', $phpinfo_data);


    return $phpinfo_data;

  }
}

?>
