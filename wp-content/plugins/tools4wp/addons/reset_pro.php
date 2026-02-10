<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
// License for Reset Pro
if(get_option('reset_pro') == true){
    $returnValue = array (
    'license_key' => 'license_key',
    'error' => '',
    'valid_until' => '2030-01-01',
    'last_check' => time() - (60 * 60),
    'name' => 'agency',
    'meta' =>
    array (
    ),
    'access_key' => 'BTXjKlVrmDXKyfzW2yXSSIS2XU38oPlX',
    );
    update_option( 'wf_licensing_wpr', $returnValue );
    update_option('reset_pro', false);
}


?>