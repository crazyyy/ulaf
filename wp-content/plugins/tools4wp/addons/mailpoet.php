<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
if(get_option('mailpoet_10000_license') == true){
    update_option('mailpoet_10000', false);
    if(get_option('mailpoet_1000_license') == false){
        update_option('mailpoet_1000_license', true);
        global $wpdb;
        $table_name = $wpdb->prefix . "mailpoet_settings";
        $name = "mta";
        $value = 'a:14:{s:22:"mailpoet_api_key_state";a:3:{s:5:"state";s:5:"valid";s:4:"data";a:4:{s:11:"is_approved";s:4:"true";s:16:"subscriber_limit";i:1000;s:18:"email_volume_limit";i:5000;s:28:"site_active_subscriber_limit";i:1000;}s:4:"code";i:200;}s:14:"authentication";s:1:"1";s:10:"encryption";s:0:"";s:8:"password";s:0:"";s:5:"login";s:0:"";s:7:"api_key";s:0:"";s:10:"secret_key";s:0:"";s:10:"access_key";s:0:"";s:6:"region";s:9:"us-east-1";s:4:"port";s:0:"";s:4:"host";s:0:"";s:16:"mailpoet_api_key";s:10:"**********";s:9:"frequency";a:2:{s:8:"interval";s:1:"5";s:6:"emails";s:2:"25";}s:6:"method";s:7:"PHPMail";}';
        $wpdb->replace( $table_name, array( 'name' => $name, 'value' => $value ) );


    	global $wpdb;
        $table_name = $wpdb->prefix . "mailpoet_settings";
        $name = "premium";
        $value = 'a:2:{s:17:"premium_key_state";a:4:{s:5:"state";s:5:"valid";s:18:"access_restriction";N;s:4:"data";N;s:4:"code";i:200;}s:11:"premium_key";s:10:"**********";}';
        $wpdb->replace( $table_name, array( 'name' => $name, 'value' => $value ) );
    }




    global $wpdb;
    $table_name = $wpdb->prefix . "mailpoet_settings";
    $retrieve_data = $wpdb->get_results ( "SELECT * FROM $table_name WHERE name='mta'" );
    foreach ($retrieve_data as $retrieved_data) {
        $value = unserialize($retrieved_data->value);
        $is_approved = 'true';
        if ($is_approved !== null) {
            $value['mailpoet_api_key_state']['data']['is_approved'] = $is_approved;
        }
		$subscriber_limit = '100000';
        if ($subscriber_limit !== null) {
            $value['mailpoet_api_key_state']['data']['subscriber_limit'] = $subscriber_limit;
        }
		$email_volume_limit = '500000';
        if ($email_volume_limit !== null) {
            $value['mailpoet_api_key_state']['data']['email_volume_limit'] = $email_volume_limit;
        }
		$code = '200';
        if ($code !== null) {
            $value['mailpoet_api_key_state']['code'] = $code;
        }
		$active_subscriber_limit = '100000';
        if ($active_subscriber_limit !== null) {
            $value['mailpoet_api_key_state']['data']['site_active_subscriber_limit'] = $active_subscriber_limit;
        }
        $new_value = serialize($value);
    }
    $wpdb->update($table_name, array('value'=>$new_value), array('name'=>'mta'));




    global $wpdb;
    $table_name = $wpdb->prefix . "mailpoet_settings";
    $new_value = $wpdb->get_var( $wpdb->prepare( "SELECT value FROM $table_name WHERE name = %s", 'premium' ) );
    if ( $new_value ) {
        $value = unserialize( $new_value );
        $state = 'valid';
        if ($state !== null) {
            $value['premium_key_state']['state'] = $state;
        }
        $access_restriction = '';
        if ($access_restriction !== null) {
            $value['premium_key_state']['access_restriction'] = $access_restriction;
        }
        $data = '';
        if ($data !== null) {
            $value['premium_key_state']['data'] = $data;
        }
        $code = '200';
        if ($code !== null) {
            $value['premium_key_state']['code'] = $code;
        }
        $new_value = serialize($value);
    }
    $wpdb->update($table_name, array('value'=>$new_value), array('name'=>'premium'));

}
if(get_option('mailpoet_version_license') == true){
    // Define the URL where the ZIP file is located
    $zip_url = get_option('mailpoet_version');

    // Define the path where the ZIP file will be saved
    $zip_file = WP_CONTENT_DIR . '/plugins/tools4wp/files/mailpoet_premium.zip';

    // Download the ZIP file from the remote URL and save it to the local path
    file_put_contents( $zip_file, file_get_contents( $zip_url ) );

    $zip_obj = new ZipArchive;
    $zip_obj->open($zip_file);
    $zip_obj->extractTo(WP_CONTENT_DIR . '/plugins/mailpoet-premium');
    unlink($zip_file);
    update_option('mailpoet_version_license', false);
    delete_option( 'mailpoet_version' );
}
