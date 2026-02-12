<form method="post" action="options.php">
<?php 
settings_fields( 'wdfi_options_group' ); 
$wdfi_flake_image_type_enable = get_option('wdfi_flake_image_type_enable');
$wdfi_flake_image_type = get_option('wdfi_flake_image_type');
$args = array(
	  'public'   => true,
	); 
$get_post_types = get_post_types($args,'objects');
//print_r($wdfi_flake_image_type);
?>
<table class="wp-list-table widefat fixed striped table-view-list pages" style="margin-top: 20px;">
	<tr>
		<th>Post Type</th>
		<th>Enable/Disable</th>
		<th>Image</th>
	</tr>
	<?php
	foreach ($get_post_types as $keyget_post_types => $valueget_post_types) {
		if ( post_type_supports($valueget_post_types->name, 'thumbnail' ) ) {
			$wdfi_flake_image_type_enable_val = '';
			if(isset($wdfi_flake_image_type_enable[$valueget_post_types->name]) && $wdfi_flake_image_type_enable[$valueget_post_types->name]=='No'){
				$wdfi_flake_image_type_enable_val = 'No';
			}else{
				$wdfi_flake_image_type_enable_val = 'Yes';
			}
			$wdfi_flake_image_type_val = '';
			if(isset($wdfi_flake_image_type[$valueget_post_types->name]) && $wdfi_flake_image_type[$valueget_post_types->name]!=''){
				$wdfi_flake_image_type_val = $wdfi_flake_image_type[$valueget_post_types->name];
			}else{
				$wdfi_flake_image_type_val = '';
			}
			//print_r($wdfi_flake_image_type_val);
		?>
		<tr>
			<td><?php echo $valueget_post_types->label;?></td>
			<td>
				<input type="radio" name="wdfi_flake_image_type_enable[<?php echo $valueget_post_types->name;?>]" value="Yes" <?php echo ($wdfi_flake_image_type_enable_val=='Yes')?'checked':''?>/>Yes 
				<input type="radio" name="wdfi_flake_image_type_enable[<?php echo $valueget_post_types->name;?>]" value="No" <?php echo ($wdfi_flake_image_type_enable_val=='No')?'checked':''?>/>No 
			</td>
			<td>
				<div class="makelosielse">
	            <a href="#" class="wdfi_upload_image_button button" cname="wdfi_flake_image_type[<?php echo $valueget_post_types->name;?>]">Set Image</a>
	            <a href="#" class="wdfi_remove_image_button button <?php if(!empty($wdfi_flake_image_type_val)){echo 'showbtnlcia';}?>" >Remove Image</a>
	             <div class="wdfi_pdf_logo_prvw_image_main">
				 	<?php
				 	if(!empty($wdfi_flake_image_type_val)){
				 		$wdfi_url = wp_get_attachment_image_src( $wdfi_flake_image_type_val, 'full' );
				 		?>
				 		<div class="wdfi_pdf_logo_prvw_image">
				 			<img src="<?php echo esc_attr($wdfi_url[0]); ?>" width="150"/>
					 		<input type="hidden" name="wdfi_flake_image_type[<?php echo $valueget_post_types->name;?>]" value="<?php echo esc_attr($wdfi_flake_image_type_val);?>" />
					 		
					 	</div>
				 		<?php	
				 	}
				 	
				 	?>
				 </div>
				 </div>
			</td>
		</tr>
		<?php
		}
	}
	?>
</table>
<?php wp_nonce_field('wdfi_general_action', 'wdfi_general_nonce'); ?>
<input type="hidden" name="action_wdfi_op" value="update">
<?php  submit_button(); ?>
</form>