<?php
 // Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// if(get_option('bultimate_license') == true){
    update_option('bultimate_license_key', base64_encode('ekOfyNen-WDHn-BXHO-aJQH-yLLnjeeLBMMl'));
    update_option('bultimate_license_status', 'valid');
    update_option('bultimate_license_details', (object)['license' => 'valid', 'success' => true, 'expires' => '22.05.2025']);
    update_option('bultimate_el', '83f8207c394d');
    update_option('bultimate_license', false);
    // }
?>