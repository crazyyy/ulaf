<?php
if (!function_exists('indeed_print_date_like_wp')):
function indeed_print_date_like_wp($date='', $print_time=TRUE){
	/*
	 * @param string
	 * @return string
	 */
	if ($date && $date!='-' && is_string($date)){
		@$date = strtotime($date);
		$format = get_option('date_format');
		$return_date = date_i18n($format, $date);
		$time = '';
		if ($print_time){
				$time_format = get_option('time_format');
				$time = date_i18n($time_format, $date);
				if ($time){
					$time = ' ' . $time;
				}
		}
		return $return_date . $time;
	}
	return $date;
}
endif;

if ( !function_exists('getFiltersFor') ):
function getFiltersFor( $hook = '' )
{
    global $wp_filter;

    if ( empty( $hook ) || !isset( $wp_filter[$hook] ) ) return [];
    $array = array();
    foreach ( $wp_filter[$hook] as $priority => $realHook ){

        foreach ($realHook as $k => $v ){

   			    $hookName = (is_array($v['function']) && isset($v['function'][0]) && is_object($v['function'][0]) ) ? get_class($v['function'][0]) . ':' . $v['function'][1] : $v['function'];
            $array[] = $hookName;

        }

    }
		return $array;
}
endif;
// cron schedule for temporary admin users
add_filter( 'cron_schedules', 'my_add_hourly_period' );
function my_add_hourly_period( $schedules )
{

    // add an 'hourly' schedule to the existing set
    $schedules['hourly'] = array(
    'interval' => 3600,
    'display' => __('Once hourly')
    );
    return $schedules;
}

if ( !function_exists('imlt_select_admins') ):
function imlt_select_admins()
{
    global $wpdb;
		$imlt_get_id_query = $wpdb->get_results("SELECT user_id FROM {$wpdb->prefix}usermeta WHERE meta_key='set_cron_time_tmp_admin' AND  meta_value<". current_time('timestamp') );
		return $imlt_get_id_query;

}
endif;

	// count admin users created in plugin
if( !function_exists('imlt_count_admin_users')) :
	function imlt_count_admin_users()
	{
		global $wpdb;
		$imlt_count_admins = $wpdb->get_var("SELECT COUNT(*) as c FROM {$wpdb->prefix}usermeta  WHERE meta_key='set_cron_time_tmp_admin' ");

		return $imlt_count_admins;
	}

endif;

	// count all users in wordpress
	if(!function_exists('imlt_all_wordpress_users')) :

		function imlt_all_wordpress_users()
		{
			$imlt_all_usr = count_users();

				return $imlt_all_usr['total_users'];

		}
endif;

	// count online users
	if(!function_exists('imlt_all_users_online')) :

		function imlt_all_users_online()
		{
			$imlt_usr_online = new ImltDatabase();
			$imlt_usr_online_count = $imlt_usr_online->selectUsersByLastLoggedTime( time() - 10 * 60 );
			$imlt_on_usr = count($imlt_usr_online_count);
			 return $imlt_on_usr;

		}
	endif;

add_action('imlt_admin_hourly_event', 'deleteAdminsCron');
if ( !function_exists('deleteAdminsCron') ):
function deleteAdminsCron()
{

		$users = imlt_select_admins();
		require_once(ABSPATH.'wp-admin/includes/user.php');
 		if ( !$users ){
				return;
		}

		foreach ($users as $user) {
				wp_delete_user($user->user_id);
		}


}
endif;


if (!function_exists('imlt_count_all_wp_tables')) {

		function imlt_count_all_wp_tables()
		{
			global $wpdb;
			$imlt_count_tables = $wpdb->get_results(" SELECT COUNT(*) AS total_tables FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$wpdb->dbname'" );

				foreach($imlt_count_tables as $imlt_count_tables_nr) {
					return $imlt_count_tables_nr->total_tables;
				}

		}
}

