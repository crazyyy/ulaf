<?php
/*
Plugin Name: WP Helper - taxonomy
Description: Some function to help developer to create and to extend taxonomy  
Plugin URI:  http://www.decristofano.it/
Version:     0.9
Author:      lucdecri
Author URI:  http://www.decristofano.it/
*/

define('WP_HELPER_TAXONOMY','0.9');
require_once ('helper_basic.php');

// add meta field to taxonomy
function add_term_customfield($term,$taxonomy,$name,$value) {
       $name = 'field_'.$taxonomy.'_'.$term;
       $data = get_option($name,'');
       if ($data=='') return;
       $data[$name]=$value;
       update_option($name,$data);
}


// add custom fields for taxonomy user interface
function register_taxonomy_fields($taxonomy,$fields) {
    global $admin_taxonomy_data;
    
    $admin_taxonomy_data[$taxonomy]= $fields;
       // aggiungo gli hook alla tassonomia $taxonomy
    add_action($taxonomy.'_add_form_fields', 'ah_taxonomy_add');
    add_action($taxonomy.'_edit_form_fields', 'ah_taxonomy_edit');
    add_action('edited_'.$taxonomy, 'ah_taxonomy_save');
    add_action('created_'.$taxonomy, 'ah_taxonomy_save');
    add_action('get_'.$taxonomy,'ah_taxonomy_get',1,2);
    
}

// inserisce un termine di tassonomia, specificando anche custom field
function insert_term($term,$taxonomy,$args) {
       
       wp_insert_term($term,$taxonomy,$args);       
       unset($args['description'],$args['parent'],$args['slug']);
       $data = serialize($args);
       $name = 'field_'.$taxonomy.'_'.$term;
       if (get_option($name,'')!='') {
		update_option($name,$data);
       } else {
		add_option($name,$data,'',true);
       }
}

// ritorna il valore di un termine di tassonomia : non serve perchè lo ritrovo in 

function get_term_customfield($field,$term,$taxonomy) {
		$name = 'field_'.$taxonomy.'_'.$term;
		$args = unserialize(get_option($name));
		return $args[$field];
}


// ritorna tutti i field di una tassonomia
function get_term_customfields($term,$taxonomy) {
		$name = 'field_'.$taxnomomy.'_'.$term;
		$args = unserialize(get_option($name));
                //if (is_array($args))  
                    return array_keys($args);
                //else        return '';
}







/* ******************** 
 * for internal use   *
 * ******************** */


// aggiunge i campi alla taxonomia, quando edito la tassonomia
function ah_taxonomy_edit($term) {
    global $admin_taxonomy_data;
    $taxonomy = $term->taxonomy;
    foreach($admin_taxonomy_data[$taxonomy] as $meta => $data) {
	admin_field($meta,$data['type'],$data['label'],$term->$meta,'',$data['message']);
    }
}

// aggiunge i campi alla tassonomia organization, quando la creo nella finestra
function ah_taxonomy_add($taxonomy) {
    global $admin_taxonomy_data;

    foreach($admin_taxonomy_data[$taxonomy] as $meta => $data) {
	admin_field($meta,$data['type'],$data['label'],'','',$data['message']);
    }
}

// salva i dati della taxonomia 
function ah_taxonomy_save( $term_id ) {
    global $admin_taxonomy_data;

    // tutti i controlli sono gia' stati fatti, o almeno dovrebbe essere cos�
    $term = get_term_by('id',$term_id,$_POST['taxonomy']);
    $slug = $term->slug;
    $taxonomy = $term->taxonomy;

    $args=array();
    foreach($admin_taxonomy_data[$taxonomy] as $meta => $data) {
	$args[$meta]=$_POST[$meta];
    }    
    
    // definisco il nome e serializzo l'array
    $data = serialize($args);
    $name = 'field_'.$taxonomy.'_'.$slug;
    if (get_option($name,'')!='') {
	    update_option($name,$data);
    } else {
	    add_option($name,$data,'',true);
    }
    
}


// modifica l'oggetto term cosi' contiene anche i custom field
function ah_taxonomy_get($term,$taxonomy) {
    global $admin_taxonomy_data;

    // deserializzo i dati
    
    $name = 'field_'.$term->taxonomy.'_'.$term->slug;
    $args = unserialize(get_option($name));
    if (is_array($args))   foreach($args as $meta=>$value) $term->$meta = $value;
    
    return $term;
}

function ah_taxonomy_get_terms($terms, $id, $taxonomy) {
    
    foreach($terms as $term_id => $term) {
        $terms[$term_id] = ah_taxonomy_get($term,$taxonomy);
    }
        
    return $terms;
    
}


// questo plugin diventa il primo eseguito : questo mi assicuare che tutti gli altri plugin hanno le funzioni messe a disposizione
function ah_taxonomy_first() {
	// ensure path to this file is via main wp plugin path
	$wp_path_to_this_file = preg_replace('/(.*)plugins\/(.*)$/', WP_PLUGIN_DIR."/$2", __FILE__);
	$this_plugin = plugin_basename(trim($wp_path_to_this_file));
	$active_plugins = get_option('active_plugins');
	$this_plugin_key = array_search($this_plugin, $active_plugins);
	if ($this_plugin_key) { // if it's 0 it's the first plugin already, no need to continue
		array_splice($active_plugins, $this_plugin_key, 1);
		array_unshift($active_plugins, $this_plugin);
		update_option('active_plugins', $active_plugins);
	}
    }

function ah_taxonomy_hooks() {
    add_filter('get_the_terms','ah_taxonomy_get_terms',1,3);
    add_action('post_edit_form_tag', 'ah_edit_form_tag');
    add_action('activated_plugin', 'ah_taxonomy_first');
    add_action('init', 'ah_set_taxonomies',50);
    add_action('admin_menu', 'ah_taxonomies_meta_boxes');
}



// Adapted from :  http://www.bundy.ca/radio-taxonomy
// thanks to : Mitchell Bundy

if (!class_exists('wpf_Walker_Category_RadioList')):
class wpf_Walker_Category_RadioList extends Walker {
	var $tree_type = 'category';
	var $db_fields = array ('parent' => 'parent', 'id' => 'term_id'); //TODO: decouple this

	function start_lvl(&$output, $depth, $args) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent<ul class='children'>\n";
	}

	function end_lvl(&$output, $depth, $args) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent</ul>\n";
	}

	function start_el(&$output, $category, $depth, $args) {
		extract($args);
		if ( empty($taxonomy) )
			$taxonomy = 'category';

		if ( $taxonomy == 'category' )
			$name = 'post_category';
		else
			$name = 'tax_input['.$taxonomy.']';

		$class = in_array( $category->term_id, $popular_cats ) ? ' class="popular-category"' : '';
		$output .= "\n<li id='{$taxonomy}-{$category->term_id}'$class>" . '<label class="selectit"><input value="' . $category->term_id . '" type="radio" name="'.$name.'[]" id="in-'.$taxonomy.'-' . $category->term_id . '"' . checked( in_array( $category->term_id, $selected_cats ), true, false ) . disabled( empty( $args['disabled'] ), false, false ) . ' /> ' . esc_html( apply_filters('the_category', $category->name )) . '</label>';
	}

	function end_el(&$output, $category, $depth, $args) {
		$output .= "</li>\n";
	}
}
endif;


function ah_set_taxonomies() {
global $wp_taxonomies;
global $ah_taxonomies;
    $ah_taxonomies=array();
    $all_tax = get_taxonomies(array(), 'objects');
    foreach ($all_tax as $tax) {
        if ($tax->show_ui==='radio') $ah_taxonomies[$tax->name]=$tax;
	$wp_taxonomies[$tax->name]->show_ui = true;
	// Default show_none to false
	if (!isset($wp_taxonomies[$tax->name]->show_none)) $wp_taxonomies[$tax->name]->show_none = false;
    }
}
		
function ah_taxonomies_meta_box($post, $metabox) {
	do_action('radio-taxonomy_box');
	$tax = $metabox['args']['taxonomy'];?>
		<div id="taxonomy-<?php echo $tax->name ?>" class="categorydiv">
            <div class="inside">
                <div id="<?php echo $tax->name; ?>-all">
                    <ul id="<?php echo $tax->name; ?>checklist" class="list:<?php echo $tax->name?> categorychecklist form-no-clear"><?php
                    // show_none set? This doesn't do much but show a radio button
                    // TODO : actually have this checked when no term is selected
                    if ($tax->show_none) {
                        echo '<li><label class="selectit"><input value="" type="radio" name="tax_input['.$tax->name.'][]"'.(apply_filters('radio-taxonomy_none-checked', false, $metabox) ? ' checked="checked"' : '').'> ';
                        echo apply_filters('radio-taxonomy_none-text', __('None', 'radio-taxonomy'), $metabox);
                        echo '</label></li>';
                    }
                    wp_terms_checklist($post->ID, array('taxonomy' => $tax->name, 'checked_ontop' => false, 'walker' => new wpf_Walker_Category_RadioList));
                    ?>
                    </ul>
                </div>
            </div>
        </div>
	<?php
	do_action('radio-taxonomy_box_after');
}
	
	
function ah_taxonomies_meta_boxes() {
global $ah_taxonomies;
		// Remove and create the new meta boxes
		foreach ($ah_taxonomies as $tax) {
			foreach ($tax->object_type as $post_type) {
				// Remove the old meta box
				remove_meta_box($tax->name.'div', $post_type, 'side');
				// Add the new meta box
				add_meta_box(
					$tax->name.'div', // id of the meta box, use the same as the old one we just removed.
					$tax->labels->singular_name, //title
					'ah_taxonomies_meta_box', // callback function that will echo the box content
					$post_type, // where to add the box: on "post", "page", or "link" page
					'side',
					'low',
					array('taxonomy' => $tax, 'post_type' => $post_type)
				);
			}
		}
}


ah_taxonomy_hooks();
?>
