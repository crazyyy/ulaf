<?php
$theme_info = wp_get_theme();
$splash_inc_path = get_template_directory() . '/includes';
$splash_partials_path = get_template_directory() . '/partials';
$splash_widgets_path = get_template_directory() . '/includes/widgets';

define( 'STM_CUSTOMIZER_PATH', get_template_directory() . '/includes/customizer' );
define( 'STM_CUSTOMIZER_URI', get_template_directory_uri() . '/includes/customizer' );
define( 'SPLASH_THEME_VERSION', ( WP_DEBUG ) ? time() : $theme_info->get( 'Version' ) );

// Product Registration
if(is_admin()) {
	require_once(get_template_directory() . '/admin/admin.php');
	require_once(get_template_directory() . '/includes/megamenu/megamenu_config.php');

    add_action('in_admin_footer', 'splash_add_current_theme', 100);

}
function splash_add_current_theme() {

    $curLayout = (splash_is_layout('baseball')) ? 'bsbl' : '';
	if(!empty($curLayout)){
		echo '<script> var currentTheme = ' . $curLayout . '</script>';
	}
    else {
	    echo '<script> var currentTheme; </script>';
    }
}

//Theme options.
require_once($splash_inc_path . '/class/Customizer_Additional.php');
require_once (STM_CUSTOMIZER_PATH . '/customizer.class.php');

// Custom code and theme main setups.
require_once( $splash_inc_path . '/setup.php' );

// Enqueue scripts and styles for theme.
require_once( $splash_inc_path . '/enqueue.php' );

// Ajax actions.
require_once( $splash_inc_path . '/ajax-actions.php' );

// Custom code for any outputs modifying.
require_once( $splash_inc_path . '/custom.php' );

function splash_sportspress_pro_url_theme_8( $url ) {
	return add_query_arg( 'theme', '8', $url );
}

function splash_check_some_other_plugin() {
    if(class_exists( 'SportsPress' )) {
        add_filter( 'sportspress_pro_url', 'splash_sportspress_pro_url_theme_8' );
    }
}
add_action( 'admin_init', 'splash_check_some_other_plugin' );

// Required plugins for the theme.
require_once( $splash_inc_path . '/tgm/tgm-plugin-registration.php' );

require_once $splash_inc_path . '/megamenu/main.php';

// Visual composer custom modules
if ( defined( 'WPB_VC_VERSION' ) ) {
	require_once( $splash_inc_path . '/visual_composer.php' );
}

/*Woocommerce setups*/
if( class_exists( 'WooCommerce' ) ) {
	require_once( $splash_inc_path . '/woocommerce.php' );
}

/*Menu Walker*/
require_once( $splash_inc_path . '/class/Split_Menu_Walker.php' );

/*Partials functions*/
/*Media single*/
require_once( $splash_partials_path . '/loop/media-content.php' );
require_once( $splash_partials_path . '/loop/media-content-3-x-3.php' );
require_once( $splash_partials_path . '/loop/media-content-grid.php' );


add_filter('woocommerce_save_account_details_required_fields', 'wc_save_account_details_required_fields' );
function wc_save_account_details_required_fields( $required_fields ){
	unset( $required_fields['account_display_name'] );
	return $required_fields;
}
function splash_add_timezone(){
	$offset = get_option('gmt_offset');
	if(floatval($offset) < 0){
		return '+' . abs(floatval($offset)) * 60;
	}
	else {
		return '-' . abs(floatval($offset)) * 60;
	}
}

function glob_pagenow(){
    global $pagenow;
    return $pagenow;
}

function glob_wpdb(){
    global $wpdb;
    return $wpdb;
}

add_filter('woocommerce_is_attribute_in_product_name', function($data){
	return false;
},100);


add_filter('login_url', function($login_url, $redirect){
	if(is_single() && function_exists('wc_get_page_permalink')) {
		$login_url = wc_get_page_permalink( 'myaccount' );
	}
	return $login_url;
}, 15, 2);


add_filter( 'woocommerce_login_redirect', function($redirect, $user){
	return get_site_url();
}, 10, 2 );

add_filter( 'splash_sportspress_event_calendar_html_output', function( $output ) {
    return $output;
});
