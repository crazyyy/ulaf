<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// License for WordFence
if(get_option('wf_license') == true){
    add_action('plugins_loaded', function(){
        if( !class_exists('wfConfig') ) return;
        wfConfig::set('isPaid', 1);
        wfConfig::set('success', 1);
        wfConfig::set('keyType', wfLicense::KEY_TYPE_PAID_CURRENT);
        wfConfig::set('licenseType', wfLicense::TYPE_RESPONSE);
        wfConfig::set('premiumNextRenew', time()+31536000);
    }, 99);
    update_option('wf_license', false);
}
?>
