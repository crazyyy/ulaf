<?php 
if(get_option('bricks_builder_license') == true){

	set_transient( 'bricks_license_status', 'active' );
	update_option( 'bricks_license_key', '3fb3b6622d0b3ea0d0fda6600aa66b8f' );
	set_transient( 'timeout_bricks_license_status', time() + 3 * 60 * 60 );
	update_option('bricksextras_license_key','******************');
	update_option('bricksextras_license_status','valid');
}
?>