<?php
/**
* This class is loaded on the back-end since its main job is
* to display the Admin to box.
*/
class WDFI_Cron {
	public function __construct () {
		add_action( 'init', array( $this, 'init' ) );
	}
	public function init(){
		$args = array(
					'label'               => __( 'wdfi', 'wdfi' ),
					'show_ui'             => false,
					'show_in_menu'        => false,
					'show_in_nav_menus'   => false,
					'show_in_admin_bar'   => false,
					'can_export'          => true,
					'has_archive'         => true,
					'exclude_from_search' => true,
					'publicly_queryable'  => true,
				);
	
		// Registering your Custom Post Type
		register_post_type( 'wdfi', $args );
		
		$defalarr = array(
			'wdfi_flake_image_type' => array(),
			//'wdfi_shop_taxo' => array(),
			/*'gmptp_single_button_text' => 'Download PDF',*/
			
		);
		foreach ($defalarr as $keya => $valuea) {
			if (get_option( $keya )=='') {
				update_option( $keya, $valuea);
			}
			
		}
		global $wdfi_rule;
		$wdfi_rule['ruls_advace'] = array();
		/*$args = array(
				    'post_type' => 'wdfi',
					'post_status' => 'publish',
					'posts_per_page' => -1
				);
		$postslist = get_posts( $args );
		$wdfi_shop_taxo_arr = array();
		if (!empty($postslist)) {
			foreach ($postslist as $postslistk => $postslistv) {
				$wdfi_shop_taxo = get_post_meta($postslistv->ID,'wdfi_shop_taxo',true);
				$makearlla = array();
				if(!empty($wdfi_shop_taxo)){
					foreach($wdfi_shop_taxo as $key_wdfi_shop_taxo=>$val_wdfi_shop_taxo){
						
						foreach($val_wdfi_shop_taxo as $key_idval=>$val_idval){
							//$makearlla[]=$key_idval;
							//print_r($key_wdfi_shop_taxo);
							$makearlla[$key_wdfi_shop_taxo][]=$key_idval;
						}
						
						
					}
				}
				
				$wdfi_shop_taxo_arr[get_post_meta($postslistv->ID,'wdfi_addnewrule_posttype',true)][]=$makearlla;
				
			}
		}
		$wdfi_shop_taxo_arr_fintal = array();
		foreach($wdfi_shop_taxo_arr as $wdfi_shop_taxo_arr_key=>$wdfi_shop_taxo_arr_val){
			foreach($wdfi_shop_taxo_arr_val as $wdfi_shop_taxo_arr_innser_key=>$wdfi_shop_taxo_arr_innser_val){
				foreach($wdfi_shop_taxo_arr_innser_val as $wdfi_shop_taxo_arr_more_key=>$wdfi_shop_taxo_arr_more_val){
					//print_r($wdfi_shop_taxo_arr_more_val);
					if(!isset($wdfi_shop_taxo_arr_fintal[$wdfi_shop_taxo_arr_key][$wdfi_shop_taxo_arr_more_key])){
						$wdfi_shop_taxo_arr_fintal[$wdfi_shop_taxo_arr_key][$wdfi_shop_taxo_arr_more_key]=array();
					}
					$wdfi_shop_taxo_arr_fintal[$wdfi_shop_taxo_arr_key][$wdfi_shop_taxo_arr_more_key]=array_merge($wdfi_shop_taxo_arr_fintal[$wdfi_shop_taxo_arr_key][$wdfi_shop_taxo_arr_more_key],$wdfi_shop_taxo_arr_more_val);
				}
			}
			
		}
		$wdfi_rule['ruls_advace'] = $wdfi_shop_taxo_arr_fintal;*/
		$args = array(
				    'post_type' => 'wdfi',
					'post_status' => 'publish',
					'posts_per_page' => -1
				);
		$postslist = get_posts( $args );
		
		if (!empty($postslist)) {
			$wdfi_shop_taxo_arr = array();
			foreach ($postslist as $postslistk => $postslistv) {
				$makearlla = array();
				$makearlla['wdfi_addnewrule_posttype']=get_post_meta($postslistv->ID,'wdfi_addnewrule_posttype',true);
				$makearlla['wdfi_shop_taxo']=get_post_meta($postslistv->ID,'wdfi_shop_taxo',true);
				$makearlla['wdfi_flake_image_type']=get_post_meta($postslistv->ID,'wdfi_flake_image_type',true);
				$wdfi_shop_taxo_arr[]=$makearlla;
				
			}
			$wdfi_rule['ruls_advace'] = $wdfi_shop_taxo_arr;
		}


		$wdfi_general=array();
		$wdfi_flake_image_type_enable = get_option('wdfi_flake_image_type_enable');
		$wdfi_flake_image_type = get_option('wdfi_flake_image_type');
		$args = array(
			  'public'   => true,
			); 
		$get_post_types = get_post_types($args,'objects');
		foreach ($get_post_types as $keyget_post_types => $valueget_post_types) {
			if ( post_type_supports($valueget_post_types->name, 'thumbnail' ) ) {
				$wdfi_flake_image_type_enable_val = '';
				if(isset($wdfi_flake_image_type_enable[$valueget_post_types->name]) && $wdfi_flake_image_type_enable[$valueget_post_types->name]=='No'){
					$wdfi_flake_image_type_enable_val = '';
				}else{
					$wdfi_flake_image_type_enable_val = 'Yes';
				}
				$wdfi_flake_image_type_val = '';
				if(isset($wdfi_flake_image_type[$valueget_post_types->name]) && $wdfi_flake_image_type[$valueget_post_types->name]!=''){
					$wdfi_flake_image_type_val = $wdfi_flake_image_type[$valueget_post_types->name];
				}else{
					$wdfi_flake_image_type_val = '';
				}
				$makearlla = array();
				$makearlla['wdfi_posttype']=$valueget_post_types->name;
				$makearlla['wdfi_flake_image_type_enable_val']=$wdfi_flake_image_type_enable_val;
				$makearlla['wdfi_flake_image_type_val']=$wdfi_flake_image_type_val;
				if( $wdfi_flake_image_type_enable_val!='' &&
					$wdfi_flake_image_type_val!='' ){
					$wdfi_general[$valueget_post_types->name]=$makearlla;
				}
				
			}
		}
		$wdfi_rule['ruls_general'] = $wdfi_general;
		//echo "<pre>";
		//print_r($wdfi_shop_taxo_arr);
		//print_r($wdfi_rule);

		//exit;
	}
}