<?php

// Exit if accessed directly

if ( ! defined( 'ABSPATH' ) ) {

    exit;

}

// License for WP Auto Spinner
if(get_option('auto_spinner_license') == true){
    update_option('wp_auto_spinner_license', 'I Love Babiato');
    update_option('wp_auto_spinner_license_active', 'active');
    update_option('wp_auto_spinner_license_active_date', time() - (60 * 60) );
    update_option('wp_auto_spinner_version_updated', time() - (60 * 60) );
    update_option('auto_spinner_license', false);
}


?>