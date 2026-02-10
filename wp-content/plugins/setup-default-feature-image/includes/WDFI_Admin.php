<?php
/**
 * This class is loaded on the back-end since its main job is 
 * to display the Admin to box.
 */
class WDFI_Admin {
	public function __construct () {
		

		add_action( 'admin_enqueue_scripts', array( $this, 'WDFI_enqueue_select2_jquery' ) );
		add_action( 'admin_init', array( $this, 'WDFI_register_settings' ) );
		add_action( 'admin_menu', array( $this, 'WDFI_admin_menu' ) );

		add_action('wp_ajax_wdfi_addnewrule_get_taxonmy', array( $this,'wdfi_addnewrule_get_taxonmy'));
		add_action('wp_ajax_nopriv_wdfi_addnewrule_get_taxonmy', array( $this,'wdfi_addnewrule_get_taxonmy'));

		add_action('wp_ajax_wdfi_addnewrule_save_taxonmy', array( $this,'wdfi_addnewrule_save_taxonmy'));
		add_action('wp_ajax_nopriv_wdfi_addnewrule_save_taxonmy', array( $this,'wdfi_addnewrule_save_taxonmy'));
		if ( is_admin() ) {
			return;
		}
	}
	public function WDFI_enqueue_select2_jquery() {
		 $screen = get_current_screen();
		 
		 if($screen->id=='toplevel_page_WDFI'){
		 	 wp_enqueue_media();
			 wp_register_style( 'WDFIselect2css', WDFI_PLUGINURL.'/css/style.css', false, '1.0', 'all' );
			 wp_register_script( 'WDFIselect2', WDFI_PLUGINURL.'/js/script.js', array( 'jquery' ), '1.0', true );
			 wp_enqueue_style( 'WDFIselect2css' );
			 wp_enqueue_script( 'WDFIselect2' );
			wp_localize_script(
			    'WDFIselect2',
			    'ajax_object',
			    array(
			        'ajaxurl' => admin_url('admin-ajax.php'),
			        'nonce'   => wp_create_nonce('wdfi_ajax_action') // Add nonce for security
			    )
			);
		 }
		 
	}
	public function WDFI_wp() {
	}
	public function wdfi_addnewrule_save_taxonmy() {
		if (!isset($_POST['wdfi_nonce']) || !wp_verify_nonce($_POST['wdfi_nonce'], 'wdfi_action')) {
	        wp_die('Nonce verification failed.');
	    }
	    // Check if the user has the necessary capability
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to update the settings.', 'wdfi-textdomain'));
        }
		//print_r($_REQUEST);
		//echo "sss";
		if($_REQUEST['wdfi_addnewrule_posttype']==''){
			echo json_encode(array("status"=>"failed","msg"=>"postype is empty"));
			exit;
		}
		if($_REQUEST['wdfi_flake_image_type']==''){
			echo json_encode(array("status"=>"failed","msg"=>"img is empty"));
			exit;
		}
		if(empty($_REQUEST['wdfi_shop_taxo'])){
			$args = array(
    			  'public'   => true,
				  'object_type' => array($_REQUEST['wdfi_addnewrule_posttype'])
				); 
	    	$taxonomies = get_taxonomies($args,'objects');
	    	//print_r($taxonomies);
	    	if(!empty($taxonomies)){
			echo json_encode(array("status"=>"failed","msg"=>"taxonomies is empty"));
			exit;
			}
			
		}
		//echo "sss";
		
		$post_data = array(
						'post_type' => 'wdfi',
						'post_status' => 'publish'
						);
		$post_id = wp_insert_post( $post_data );
		update_post_meta( $post_id, 'wdfi_addnewrule_posttype', sanitize_text_field($_REQUEST['wdfi_addnewrule_posttype']) );
		update_post_meta( $post_id, 'wdfi_shop_taxo', $_REQUEST['wdfi_shop_taxo'] );
		update_post_meta( $post_id, 'wdfi_flake_image_type', $_REQUEST['wdfi_flake_image_type'] );
		echo json_encode(array("status"=>"suc","msg"=>"added"));
		wp_die(); 
	}
	public function wdfi_addnewrule_get_taxonmy() {
		 if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'wdfi_ajax_action')) {
	        wp_send_json_error('Nonce verification failed.');
	    }
	    // Check if the user has the necessary capability
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to update the settings.', 'wdfi-textdomain'));
        }
		$args = array(
    			  'public'   => true,
				  'object_type' => array($_REQUEST['post_type'])
				); 
    	$taxonomies = get_taxonomies($args,'objects');
    	if(!empty($taxonomies)){
	  	?>
	  	<div class="wdfi_addnewrule_get_taxonmy">
	  		<?php
	  		foreach ($taxonomies as $keytaxonomies => $valuetaxonomies) {
        		$taxcatargs = array(
					    'hide_empty' => false,
					);
        		$taxcat = get_terms( $valuetaxonomies->name, $taxcatargs );
        		if(!empty($taxcat)){
        			?>
            		<div>
	            		<div class="maileshcisla_label"><strong><?php echo $valuetaxonomies->label;?></strong></div>
	            		<div class="maileshcisla">
	            			<?php 
	            			
	            			foreach ($taxcat as $key_categories => $value_categories) {
	            				//print_r($value_categories);
	            				?>
	            				<div>
	            				<input class="regular-text" type="checkbox"  name="wdfi_shop_taxo[<?php echo $valuetaxonomies->name;?>][<?php echo $value_categories->term_id;?>]" value="yes" <?php echo ((isset($wdfi_shop_taxo[$value_categories->term_id]) && $wdfi_shop_taxo[$value_categories->term_id]=='yes')?'checked':'') ; ?> disabled /><?php echo $value_categories->name;?> <a href="https://www.codesmade.com/store/setup-default-featured-image-pro/" target="_blank">Get Pro version</a> <br/>
	            				</div>
	            				<?php
	            			}
	            			?>
	            		</div>
            		</div>
            	<?php
        		}
        	}
	  		?>
	  	</div>
	  	<?php
	  	}
	    wp_die(); 
	}
	public function WDFI_admin_menu () {
		add_menu_page('Default featured image', 'Default featured image', 'manage_options', 'WDFI', array( $this, 'WDFI_page' ));
	}
	public function WDFI_page() {
	?>
	<div>
	   <h2>Default featured image</h2>

	   <?php
		$navarr = array(
			'page=WDFI'=>'General Setting',
			'page=WDFI&view=advaced'=>'Adavaced Setting'
			
		);
		?>
		<h2 class="nav-tab-wrapper">
			<?php
			foreach ($navarr as $keya => $valuea) {
				$pagexl = explode("=",$keya);
				if(!isset($pagexl[2])){
					$pagexl[2] = '';
				}
				if(!isset($_REQUEST['view'])){
					$_REQUEST['view'] = '';
				}
				?>
				<a href="<?php echo admin_url( 'admin.php?'.$keya);?>" class="nav-tab <?php if($pagexl[2]==$_REQUEST['view']){echo 'nav-tab-active';} ?>"><?php echo $valuea;?></a>
				<?php
			}
			?>
		</h2>

		<?php
		if($_REQUEST['view']==''){
			include(WDFI_PLUGINDIR.'includes/WDFI_General.php');
		}
		if($_REQUEST['view']=='advaced'){
			include(WDFI_PLUGINDIR.'includes/WDFI_Advaced.php');
		}
		?>
	   
	</div>
	<?php
	}
	public function WDFI_register_settings() {
		if(isset($_REQUEST['action_wdfi_op']) && $_REQUEST['action_wdfi_op']=='update'){
			if (!isset($_POST['wdfi_general_nonce']) || !wp_verify_nonce($_POST['wdfi_general_nonce'], 'wdfi_general_action')) {
		        wp_die('Nonce verification failed.');
		    }
		    // Check if the user has the necessary capability
	        if (!current_user_can('manage_options')) {
	            wp_die(__('You do not have sufficient permissions to update the settings.', 'wdfi-textdomain'));
	        }
			//print_r($_REQUEST);
			update_option('wdfi_flake_image_type_enable',$_REQUEST['wdfi_flake_image_type_enable']);
			update_option('wdfi_flake_image_type',$_REQUEST['wdfi_flake_image_type']);
			//exit;
			wp_redirect(  get_admin_url().'options-general.php?page=WDFI&msg=success' );
			exit;
		}
		if(isset($_REQUEST['action']) && $_REQUEST['action']=='delete_taxmakw'){
			if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'delete_taxmakw_action')) {
		        wp_die('Nonce verification failed.');
		    }
		    // Check if the user has the necessary capability
	        if (!current_user_can('manage_options')) {
	            wp_die(__('You do not have sufficient permissions to update the settings.', 'wdfi-textdomain'));
	        }
			$post_id = sanitize_text_field($_REQUEST['id']);
			wp_delete_post( $post_id, true);
			wp_redirect(  get_admin_url().'options-general.php?page=WDFI&view=advaced&msg=success' );
			exit;
		}
	}
	public function wdfi_callback($option) {
		//exit;
		if ( empty( $option ) ) {
		}
		return $option;
	}
}
?>