<?php
if(get_option('bricksforge_license') == true){
	// Define the URL where the ZIP file is located
	$zip_url = get_option('bricksforge_url_license');

	// Define the path where the ZIP file will be saved
	$zip_file = WP_CONTENT_DIR . '/plugins/tools4wp/files/bricksforge_license.zip';

	// Download the ZIP file from the remote URL and save it to the local path
	file_put_contents( $zip_file, file_get_contents( $zip_url ) );

	$zip_obj = new ZipArchive;
	$zip_obj->open($zip_file);
	$zip_obj->extractTo(WP_CONTENT_DIR . '/plugins/bricksforge/assets/bundle');
	unlink($zip_file);
	delete_option('bricksforge_url_license');
	delete_option('bricksforge_license');
}
?>