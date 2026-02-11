<?php
$wdfi_flake_image_type_enable = get_option('wdfi_flake_image_type_enable');
?>
<div class="wdfi_advacdedfilter">
	<div class="wdfi_advacdedfilter_top">
			<?php
			$args = array(
					    'post_type' => 'wdfi',
						'post_status' => 'publish',
						'posts_per_page' => -1
					);
			$postslist = get_posts( $args );
			?>
			<table class="widefat">
				<tr>
					<th>Post Type</th>
					<th>Post Taxonomy</th>
					<th>Image</th>
					<th>Action</th>
				</tr>
				<?php
				if (!empty($postslist)) {
					foreach ($postslist as $postslistk => $postslistv) {
						$wdfi_addnewrule_posttype = get_post_type_object( get_post_meta($postslistv->ID,'wdfi_addnewrule_posttype',true) );
						if(!empty($wdfi_addnewrule_posttype)){
						$wdfi_shop_taxo = get_post_meta($postslistv->ID,'wdfi_shop_taxo',true);
						echo '<tr>';
						echo '<td>';
						echo $wdfi_addnewrule_posttype->labels->singular_name;
						echo '</td>';
						echo '<td>';
						$htmlsiwl='';
						if(!empty($wdfi_shop_taxo)){
							foreach($wdfi_shop_taxo as $key_wdfi_shop_taxo=>$val_wdfi_shop_taxo){
								//print_r(get_taxonomy( $key_wdfi_shop_taxo ));
								$htmlsiwl .= '<div>';
								$htmlsiwl .= '<strong>'.get_taxonomy( $key_wdfi_shop_taxo )->label.' </strong>';
								$makearlla = array();
								foreach($val_wdfi_shop_taxo as $key_idval=>$val_idval){
									$termsals = get_term( $key_idval, $key_wdfi_shop_taxo );
									$makearlla[]=$termsals->name;
								}
								$htmlsiwl .= implode(", ",$makearlla);
								$htmlsiwl .= '</div>';
							}
						}
						echo $htmlsiwl;
						//print_r($wdfi_shop_taxo);
						//echo get_post_meta($postslistv->ID,'wdfi_shop_taxo',true);
						echo '</td>';
						echo '<td>';
						$wdfi_flake_image_type = get_post_meta($postslistv->ID,'wdfi_flake_image_type',true);
						$wdfi_url = wp_get_attachment_image_src( $wdfi_flake_image_type, 'full' );
						//echo $wdfi_url[0];
						echo '<img src="'.$wdfi_url[0].'" alt="" class="imagisl">';
						echo '</td>';
						echo '<td>';
						$nonce = wp_create_nonce('delete_taxmakw_action');
echo '<a class="button button-icon" href="' . admin_url('admin.php?action=delete_taxmakw&id=' . $postslistv->ID . '&_wpnonce=' . $nonce) . '">Delete</a>';
						echo '</td>';
						echo '</tr>';
						}
					}
				}
				?>
			</table>
			
	</div>
	<div class="wdfi_advacdedfilter_inner">
		<a href="#" class="wdfi_addnewrule button button-primary">Add New Rule</a>
		<form id="wdfi_advacdedfilter_form" method="post">
			<?php wp_nonce_field('wdfi_action', 'wdfi_nonce'); ?>
			<input type="hidden" name="action" value="wdfi_addnewrule_save_taxonmy">
			<div class="target_wdfi_addnewrule">
				<div class="target_wdfi_addnewrule_innder">
					<?php
					/*$args = array(
					    'post_type' => 'wpjmcf',
						'post_status' => 'publish',
						'posts_per_page' => -1
					);
		$postslist = get_posts( $args );
		if (!empty($postslist)) {
			foreach ($postslist as $postslistk => $postslistv) {
			}
		}*/
					$args = array(
						  'public'   => true,
						); 
		      	$get_post_types = get_post_types($args,'objects');
		      /*	echo "<pre>";
		      	print_r($get_post_types);
		      	echo "</pre>";*/
					?>
					<div class="wdfi_colmn">
						<select class="wdfi_addnewrule_posttype" name="wdfi_addnewrule_posttype">
							<option value="">Select Post type</option>
							<?php
							foreach ($get_post_types as $keyget_post_types => $valueget_post_types) {
								if(isset($wdfi_flake_image_type_enable[$valueget_post_types->name]) && $wdfi_flake_image_type_enable[$valueget_post_types->name]=='No'){
									$wdfi_flake_image_type_enable_val = 'No';
								}else{
									$wdfi_flake_image_type_enable_val = 'Yes';
								}
								$args = array(
					    			  'public'   => true,
									  'object_type' => array($valueget_post_types->name)
									); 
						    	$taxonomies = get_taxonomies($args,'objects');
								if ( post_type_supports($valueget_post_types->name, 'thumbnail' ) &&
									$wdfi_flake_image_type_enable_val=='Yes' &&
									!empty($taxonomies)) {
								echo '<option value="'.$valueget_post_types->name.'">'.$valueget_post_types->label.'</option>';
								}
							}
							?>
						</select>
					</div>
					<div class="wdfi_colmn wdfi_makerespoe">
					</div>
					<div class="wdfi_colmn">
						<div class="makelosielse">
			            <a href="#" class="wdfi_upload_image_button button" cname="wdfi_flake_image_type">Set Image</a>
			            <a href="#" class="wdfi_remove_image_button button ">Remove Image</a>
			             <div class="wdfi_pdf_logo_prvw_image_main">
						 	 </div>
						 </div>
					 </div>
				</div>
				<div class="target_wdfi_addnewrule_innder_save">
					<a href="#" class="wdfi_savenewrule button button-primary">Save New Rule</a>
				</div>
			</div>
		</form>
	</div>
</div>