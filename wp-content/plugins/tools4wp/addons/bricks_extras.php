<?php 
if(get_option('bricks_extras_license') == true){

	update_option('bricksextras_license_key','******************');
	update_option('bricksextras_license_status','valid');
	update_option('bricks_extras_license', false);
}
?>