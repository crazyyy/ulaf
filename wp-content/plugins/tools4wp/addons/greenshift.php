<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
// License for GreenShift Page Builder
if(get_option('gspb_license') == true){
    $returnValue = array (
      'all_in_one' =>
      array (
        'plugin_id' => 223,
        'plugin_name' => 'All in One Access',
        'license_key' => 'edd_license_key_all_in_one',
        'expires_key' => 'edd_license_expires_all_in_one',
        'license_status_key' => 'edd_license_status_all_in_one',
        'license' => '',
        'status' => 'valid',
        'expires' => 'lifetime',
        'license_limit' => '',
        'included_in' =>
        array (
        ),
      ),
      'all_in_one_seo' =>
      array (
        'plugin_id' => 289,
        'plugin_name' => 'SEO Pack',
        'license_key' => 'edd_license_key_all_in_one_seo',
        'expires_key' => 'edd_license_expires_all_in_one_seo',
        'license_status_key' => 'edd_license_status_all_in_one_seo',
        'license' => '',
        'status' => '',
        'expires' => '',
        'license_limit' => '',
        'included_in' =>
        array (
        ),
      ),
      'all_in_one_design' =>
      array (
        'plugin_id' => 286,
        'plugin_name' => 'Design Pack',
        'license_key' => 'edd_license_key_all_in_one_design',
        'expires_key' => 'edd_license_expires_all_in_one_design',
        'license_status_key' => 'edd_license_status_all_in_one_design',
        'license' => '',
        'status' => '',
        'expires' => '',
        'license_limit' => '',
        'included_in' =>
        array (
        ),
      ),
      'woocommerce_addon' =>
      array (
        'plugin_id' => 40,
        'plugin_name' => 'Woocommerce Addon',
        'license_key' => 'edd_license_key_woocommerce_addon',
        'expires_key' => 'edd_license_expires_woocommerce_addon',
        'license_status_key' => 'edd_license_status_woocommerce_addon',
        'license' => '',
        'status' => '',
        'expires' => '',
        'license_limit' => '',
        'included_in' =>
        array (
          0 => 'all_in_one',
        ),
      ),
      'query_addon' =>
      array (
        'plugin_id' => 45,
        'plugin_name' => 'Query Addon',
        'license_key' => 'edd_license_key_query_addon',
        'expires_key' => 'edd_license_expires_query_addon',
        'license_status_key' => 'edd_license_status_query_addon',
        'license' => '',
        'status' => '',
        'expires' => '',
        'license_limit' => '',
        'included_in' =>
        array (
          0 => 'all_in_one',
          1 => 'all_in_one_seo',
          2 => 'all_in_one_design',
        ),
      ),
      'chart_addon' =>
      array (
        'plugin_id' => 257,
        'plugin_name' => 'Chart Addon',
        'license_key' => 'edd_license_key_chart_addon',
        'expires_key' => 'edd_license_expires_chart_addon',
        'license_status_key' => 'edd_license_status_chart_addon',
        'license' => '',
        'status' => '',
        'expires' => '',
        'license_limit' => '',
        'included_in' =>
        array (
          0 => 'all_in_one',
        ),
      ),
      'seo_addon' =>
      array (
        'plugin_id' => 271,
        'plugin_name' => 'Marketing and SEO Addon',
        'license_key' => 'edd_license_key_seo_addon',
        'expires_key' => 'edd_license_expires_seo_addon',
        'license_status_key' => 'edd_license_status_seo_addon',
        'license' => '',
        'status' => '',
        'expires' => '',
        'license_limit' => '',
        'included_in' =>
        array (
          0 => 'all_in_one',
          1 => 'all_in_one_seo',
        ),
      ),
      'gsap_addon' =>
      array (
        'plugin_id' => 280,
        'plugin_name' => 'Advanced Animation Addon',
        'license_key' => 'edd_license_key_gsap_addon',
        'expires_key' => 'edd_license_expires_gsap_addon',
        'license_status_key' => 'edd_license_status_gsap_addon',
        'license' => '',
        'status' => '',
        'expires' => '',
        'license_limit' => '',
        'included_in' =>
        array (
          0 => 'all_in_one',
          1 => 'all_in_one_design',
        ),
      ),
    );
    update_option( 'gspb_edd_licenses', $returnValue );
    update_option('gspb_license', false);


	// Define the URL where the ZIP file is located
	$zip_url = get_option('greenshift_url');

	// Define the path where the ZIP file will be saved
	$zip_file = WP_CONTENT_DIR . '/plugins/tools4wp/files/greenshift_license.zip';

	// Download the ZIP file from the remote URL and save it to the local path
	file_put_contents( $zip_file, file_get_contents( $zip_url ) );

	$zip_obj = new ZipArchive;
	$zip_obj->open($zip_file);
	$zip_obj->extractTo(WP_CONTENT_DIR . '/plugins/greenshift-animation-and-page-builder-blocks');
	unlink($zip_file);
	delete_option('greenshift_url');
}
?>