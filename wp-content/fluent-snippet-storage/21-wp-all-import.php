<?php
// <Internal Doc Start>
/*
*
* @description: Upload all images for the post to a folder based on the post date (in Y/m format). For example, if the post was published on June 1st 2017, its images would be uploaded to /wp-content/uploads/2017/06.


* @tags: 
* @group: 
* @name: WP All Import - Upload images to folder based on post date
* @type: PHP
* @status: draft
* @created_by: 1
* @created_at: 2026-02-13 23:55:33
* @updated_at: 2026-02-13 23:55:33
* @is_valid: 1
* @updated_by: 1
* @priority: 10
* @run_at: all
* @load_as_file: 
* @load_in_block_editor: 
* @condition: {"status":"no","run_if":"assertive","items":[[]]}
*/
?>
<?php if (!defined("ABSPATH")) { return;} // <Internal Doc End> ?>
<?php
if ( ! function_exists( 'wpcb_set_import_img_upload_folder_by_post_date' ) ) {
 function wpcb_set_import_img_upload_folder_by_post_date($uploads, $articleData, $current_xml_node, $import_id) {
	if ( ! empty($articleData['post_date'])) {
		$uploads['path'] = $uploads['basedir'] . '/' . date("Y/m", strtotime($articleData['post_date']));
		$uploads['url'] = $uploads['baseurl'] . '/' . date("Y/m", strtotime($articleData['post_date']));
		
		if (!file_exists($uploads['path'])) {
			mkdir($uploads['path'], 0755, true);
		}
	}
	return $uploads;
}   
}

add_filter('wp_all_import_images_uploads_dir', 'wpcb_set_import_img_upload_folder_by_post_date', 99, 4);