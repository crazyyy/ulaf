<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
if(get_option('updraftplus_addons') == true){
    // Define the URL where the ZIP file is located
    $zip_url = get_option('updraftplus_url');

    // Define the path where the ZIP file will be saved
    chmod(WP_CONTENT_DIR . '/plugins/tools4wp/files', 0755);
    $zip_file = WP_CONTENT_DIR . '/plugins/tools4wp/files/updraftplus_addons.zip';

    // Download the ZIP file from the remote URL and save it to the local path
    file_put_contents( $zip_file, file_get_contents( $zip_url ) );

    $zip_obj = new ZipArchive;
    $zip_obj->open($zip_file);
    $zip_obj->extractTo(WP_CONTENT_DIR . '/plugins/updraftplus');
    unlink($zip_file);
    update_option('updraftplus_addons', false);
    delete_option('updraftplus_url');
}

?>