<?php

// Exit if accessed directly

if ( ! defined( 'ABSPATH' ) ) {

    exit;

}

// License for Breakdance
if(get_option('breakdance_license') == true){
    $validity_info = json_decode(get_option('breakdance_license_key_validity_info'), true);
    if($validity_info) {
        $validity_info['license_key'] = str_replace('"', '', get_option('breakdance_license_key'));
        $validity_info['intended_subscription_mode'] = 'pro';
		$validity_info['edd_item_id'] = '14';
        $validity_info['edd_key_info']['item_name'] = 'Breakdance Pro';
        $validity_info['edd_key_info']['expires'] = 'lifetime';
        $validity_info['edd_key_info']['license'] = 'valid';
		$validity_info['edd_key_info']['item_id'] = '14';
        $validity_info['checked_at'] = time() - (60 * 60);
        update_option('breakdance_license_key_validity_info', json_encode($validity_info));
    } else {
        $validity_info = array(
            'intended_subscription_mode' => 'pro',
            'edd_item_id' => 14,
            'license_key' => bin2hex(random_bytes( 16 )),
            'edd_key_info' => array(
                'success' => true,
                'license' => 'valid',
                'item_id' => 14,
                'item_name' => 'Breakdance Pro',
                'checksum' => bin2hex(random_bytes( 16 )),
                'expires' => 'lifetime',
                'payment_id' => bin2hex(random_bytes( 2 )),
                'customer_name' =>  'Tools4WP',
                'customer_email' => 'breakdance@tools4wp.com',
                'license_limit' => 0,
                'site_count' => 1,
                'activations_left' => 'unlimited',
                'price_id' => false,
            ),
            'checked_at' => time() - (60 * 60),
        );
        update_option('breakdance_license_key_validity_info', json_encode($validity_info));
	}	
}

?>