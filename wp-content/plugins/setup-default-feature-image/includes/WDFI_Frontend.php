<?php
/**
 * This class is loaded on the back-end since its main job is 
 * to display the Admin to box.
 */
class WDFI_Frontend {
	public function __construct () {
		add_filter( 'get_post_metadata', array( $this, 'WDFI_get_post_metadata' ), 50, 10);
		add_filter( 'post_thumbnail_html', array( $this, 'WDFI_post_thumbnail_html' ), 100, 10);
	}
	public function WDFI_get_post_metadata($null, $object_id, $meta_key, $single ){
	        if ( ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) ) {
	                return $null;
	        }
	        if ( ! empty( $meta_key ) && '_thumbnail_id' !== $meta_key ) {
	                return $null;
	        }
	        $post_type = get_post_type( $object_id );
	        if ( false !== $post_type && ! post_type_supports( $post_type, 'thumbnail' ) ) {
	                return $null; 
	        }
	        $meta_cache = wp_cache_get( $object_id, 'post_meta' );
	        if ( ! $meta_cache ) {
	                $meta_cache = update_meta_cache( 'post', array( $object_id ) );
	                if ( ! empty( $meta_cache[ $object_id ] ) ) {
	                        $meta_cache = $meta_cache[ $object_id ];
	                } else {
	                        $meta_cache = array();
	                }
	        }
	        if ( ! empty( $meta_cache['_thumbnail_id'][0] ) ) {
	                return $null;
	        }
	        //echo $object_id;
	        //if($this->WDFI_is_exclude($object_id)==false){
	        	$default_thumbnail_id =  $this->WDFI_is_return_id($object_id);
		        $meta_cache['_thumbnail_id'][0] = $default_thumbnail_id;
		        wp_cache_set( $object_id, $meta_cache, 'post_meta' );
	       // }
	        
	        
	        return $null;
	}
	public function WDFI_post_thumbnail_html( $html, $post_id, $post_thumbnail_id, $size, $attr ) {

	        $default_thumbnail_id =  $this->WDFI_is_return_id($post_id);
	       //echo $post_thumbnail_id;
	        if ( (int) $default_thumbnail_id !== (int) $post_thumbnail_id ||  
	        	$default_thumbnail_id==0 ) {
	        	
	                return $html;
	        }
	       // echo "ss";
	        //if($this->WDFI_is_exclude($post_id)==false){
	        	if ( isset( $attr['class'] ) ) {
	                $attr['class'] .= ' gm-default-featured-img';
		        } else {
		                $size_class = $size;
		                if ( is_array( $size_class ) ) {
		                        $size_class = 'size-' . implode( 'x', $size_class );
		                }$attr = array( 'class' => "attachment-{$size_class} default-featured-img" );
		        }
		        $html = wp_get_attachment_image( $default_thumbnail_id, $size, false, $attr );
	        //}
	        
	        return $html;
	}
	public function WDFI_is_return_id($post_id) {
		global $wdfi_rule;
		$post_type = get_post_type( $post_id);
		$media_id = 0;
		$args = array(
    			  'public'   => true,
				  'object_type' => array($post_type)
				); 
    	$taxonomies = get_taxonomies($args,'objects');
    	$maketaxarr = array();
    	if(!empty($taxonomies)){
    		foreach ($taxonomies as $keytaxonomies => $valuetaxonomies) {
    			$terms = get_the_terms( $post_id, $valuetaxonomies->name ); 
    			if ( $terms && ! is_wp_error( $terms ) ) {
				    $term_ids = wp_list_pluck( $terms, 'term_id' );
				    $maketaxarr[$valuetaxonomies->name] = $term_ids;
				}
    		}
    	}
    	//print_r($maketaxarr);
    	//print_r($wdfi_rule);
		/*echo "<pre>";
		print_r($wdfi_rule);
		echo "</pre>";*/
		//print_r($wdfi_rule['ruls_general']);
		if(isset($wdfi_rule['ruls_general'][$post_type])){
			//echo "sss";
			$media_id = $wdfi_rule['ruls_general'][$post_type]['wdfi_flake_image_type_val'];
		}
		//echo "sss";
		if(!empty($wdfi_rule['ruls_advace'])){
			foreach($wdfi_rule['ruls_advace'] as $ruls_advace_key => $ruls_advace_val){
				if($ruls_advace_val['wdfi_addnewrule_posttype'] == $post_type){
					if(!empty($ruls_advace_val['wdfi_shop_taxo'])){
						foreach($ruls_advace_val['wdfi_shop_taxo'] as $wdfi_shop_taxo_key => $wdfi_shop_taxo_val){
							//print_r($wdfi_shop_taxo_key);
							//print_r(array_keys($wdfi_shop_taxo_val));
							if(!empty($maketaxarr[$wdfi_shop_taxo_key])){
								$result = array_intersect($maketaxarr[$wdfi_shop_taxo_key], array_keys($wdfi_shop_taxo_val));
								//print_r($result);
								$media_id = $ruls_advace_val['wdfi_flake_image_type'];
							}
							/*$taxcatargs = array(
								    'hide_empty' => false,
								    'taxonomy' => $wdfi_shop_taxo_key,
								    'include'=>
								);
			        		$taxcat = get_terms( , $taxcatargs );*/

						}

					}
				}
			}
		}
		
		/*print_r($wdfi_flake_image_type_enable);
		print_r($wdfi_flake_image_type);
		echo $media_id;*/
		return $media_id;
	}
}