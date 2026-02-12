<?php
/*
Plugin Name: WP Helper - posttype
Description: Some function to help developer to create and to extend post type  
Plugin URI:  http://www.decristofano.it/
Version:     0.9
Author:      lucdecri
Author URI:  http://www.decristofano.it/
*/

define('WP_HELPER_POSTTYPE','0.9');
require_once ('helper_basic.php');

// registra un box per un posttype
function register_post_box($posttype,$boxname,$description,$position,$priority,$form_fields) {
/* il singolo campo ha le seguenti opzioni
 * type         => indica il tipo di campo. vedi admin_field
 * hidden       => se true, il campo e' nascosto a prescindere dal type
 * readonly     => se true, il campo e' in sola lettura a prescindere dal type
 * default      => indica il valore da assegnare al campo nel caso non ve ne sia uno memorizzato
 * option       => indica il valore da assegnare al campo opzionale nel caso non ve ne sia uno memorizzato
 * values       => indica i valori tra cui vengono scelte le opzioni (nel caso, ad esempio di una select)
 * description  => il testo di descrizione del campo
 * howto        => il testo di spiegazione
 * closer       => un testo subito dopo l'input @TODO closer option
 * automation   => va inserito nel tag dell'input @TODO automation option
 * intable      => se true, il campo � visibile anche nella tabella riassuntiva dei posttype
 * orderable    => se true e se intable � true, � ordinabile nella tabella riassuntiva dei posttype
 * filterable   => se true e se intable � true, � presente una select per filtrare nella tabella riassuntiva dei posttype
 */
    
global $admin_post_data;
global $admin_boxes_data;
    
    if ($form_fields) foreach($form_fields as $k=>$d)
                $admin_post_data[$posttype][$boxname][$k]=$d;
    
    $admin_boxes_data[$boxname]= array(
			    'type' => $posttype,
			    'description' => $description,
			    'position' => $position,
			    'priority' => $priority,
    			'callback' => 'ah_add_box'
			);
}

// registra un box per assegnare il parent di un post
function register_post_parentbox($posttype,$description,$query_array) {
	global $admin_post_data;
	global $admin_boxes_data;

	$posts = get_posts($query_array);
	$values = '';
	foreach($posts as $id => $val ) $values .= $val->ID.':'.$val->post_title.',';
	$values = trim($values,',');
	register_post_box($posttype,$posttype.'_parent',$description,'side','default',array(
			$posttype.'_parent' => array(
					'type' => 'parent',
					'description'=>__($description, ADLEEX_DOMAIN ),
					'values' => $values,
					'howto' => 'altro',
					)
			)
	);
		
}

// registra un box per un posttype che richiama una callback
function register_post_externalbox($posttype,$boxname,$description,$position,$priority,$callback) {
	global $admin_boxes_data;	
	$admin_boxes_data[$boxname]= array(
			'type' => $posttype,
			'description' => $description,
			'position' => $position,
			'priority' => $priority,
			'callback' => $callback
	);

}

function set_postmeta($id,$name,$value) {
// save post meta named $name. Create new if don't exist, overwrite if exist
  if (!get_post_meta($id,$name)) 
        add_post_meta($id,$name,$value,true);
  else 
        update_post_meta($id,$name,$value);
}

function set_postmeta_multiple($id,$name,$value) {
// save post meta named $name. Create new if don't exist, add new if exist
        add_post_meta($id,$name,$value,true);
}





/* ******************** 
 * for internal use   *
 * ******************** */


function ah_add_boxes() {
global $admin_boxes_data;

    if ($admin_boxes_data==array()) return;
    foreach($admin_boxes_data as $boxname => $data)
		add_meta_box( $boxname, $data['description'], $data['callback'], $data['type'], $data['position'], $data['priority'], '' );

}


function ah_add_box($post,$box, $localization = '') {
global $admin_post_data;

    $boxname=$box['id'];
    $post_type = $post->post_type;
    
    $fields = $admin_post_data[$post_type][$boxname];
    wp_nonce_field( plugin_basename( __FILE__ ), $post_type.'_'.$boxname.'_noncename' );
    foreach($fields as $name => $field) {
	      $field = apply_filters($post_type.'_field', $field, $name);
          $field = apply_filters($post_type.'_'.$boxname.'_field', $field, $name);
          
          $field['label']=$field['description'];
          
          $value = get_post_meta($post->ID,$name,true);
	      if ($value) $field['default']=$value;
	      if ($field['type']=='date') {
			//$value = date_create_from_format('Y-m-d',$value);
			//if (!is_object($value)) $value = new DateTime();
			//$value = $value->format('d-m-Y');
          }
          $value_option = get_post_meta($post->ID,$name.'_option',true);
          if ($value_option) $field['option']=$value_option;
	      echo ah_form_field($name, $field, 'form', $localization );

    }

}

function old_ah_add_box($post,$box) {
	global $admin_post_data;

	$boxname=$box['id'];
	$post_type = $post->post_type;

	$fields = $admin_post_data[$post_type][$boxname];
	wp_nonce_field( plugin_basename( __FILE__ ), $post_type.'_'.$boxname.'_noncename' );
	foreach($fields as $name => $field) {
		$field = apply_filters($post_type.'_field', $field, $name);
		$field = apply_filters($post_type.'_'.$boxname.'_field', $field, $name);
		if        (@$field['hidden'])     $type='hidden';
		elseif    (@$field['readonly'])   $type='readonly';
		else                              $type = $field['type'];

		$description = $field['description'];
		if (@$field['values']) $description .= '|'. $field['values'];
		if (@$field['closer']) $description .= '|'. $field['closer']; //@FIXME se values non e' presente, non e' corretto
		 
		$value = get_post_meta($post->ID,$name,true);
		if (!$value) $value=@$field['default'];
		if ($field['type']=='date') {
			//$value = date_create_from_format('Y-m-d',$value);
			//if (!is_object($value)) $value = new DateTime();
			//$value = $value->format('d-m-Y');
		}
		$value_option = get_post_meta($post->ID,$name.'_option',true);
		if (!$value_option) $value_option=@$field['option'];
		 
		admin_field($name, $type, $description, $value, '', @$field['howto'] );
	}

}


function ah_post_insert($data,$postarr) {
global $admin_post_data_value;
global $admin_post_data;
    $type = $data['post_type'];
    if (key_exists($type,$admin_post_data)) {
	foreach($admin_post_data[$type] as $boxname => $fields) {
	    foreach($fields as $name => $field) {
		    if (isset($postarr[$name])) $admin_post_data_value[$postarr['ID']][$name] = $postarr[$name];
		    else $admin_post_data_value[$postarr['ID']][$name] = get_post_meta($postarr['ID'],$name,true);
	    }
	}
    }
    $admin_post_data_value[$postarr['ID']]['post_type'] = $type;
    return $data;
}

function ah_post_get($post) {
global $admin_post_data;
    $type = $post->post_type;
    if (key_exists($type,$admin_post_data)) {
	foreach($admin_post_data[$type] as $boxname => $fields) {
	    foreach($fields as $name => $field) {
		    $value = get_post_meta($post->ID,$name,true);
		    if ($field['type']=='date') {
			$value = date_create_from_format('Y-m-d',$value);
			if (!is_object($value)) $value=new DateTime();
			$value = $value->format('d-m-Y');
		    }
		    $post->$name =  $value;
	    }
	}
    }
}

function ah_posttype_first() {
        execute_thisplugin_first(__FILE__);
}

function ah_posttype_loaded() {
    global $admin_post_data;	
	$admin_post_data = array();
}

// salva i dati del post.
// Purtroppo devo date una priorit√† tra di dati che ricevo.
// i dati in $_POST (relativi a quell'id) hanno la priorit√† su quelli in $post
function ah_post_save($post_id,$post) {
global $admin_post_data_value;
global $admin_post_data;

    // verifico se sto salvando da un mediatype(o elenco di post), da un form singolo o da codice
  $verified=false;
  //@TODO devo correggere anche la $files perchè così non funziona quando ho i mediatype
  if (isset($_POST['attachments'][$post_id])) 
        {$data=$_POST['attachments'][$post_id]; $verified=true; $files = @$_FILES[$post_id]; }// è un mediatype o un elenco di post
  elseif (isset($_POST['post_ID']) && ($_POST['post_ID']==$post_id) ) 	
        { $data=$_POST;  $files = @$_FILES; } // è da pannello admin
  elseif (isset($admin_post_data_value[$post_id]))  
        { $data=$admin_post_data_value[$post_id];$verified=true; $files=array(); } // è da codice ed è un update
  else	
        { $data=$admin_post_data_value[0];$verified=true; $admin_post_data_value[0]=NULL;} // √® da codice ed √® un inserimento o non ha campi custom 

  $type = $post->post_type;
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )   return;
  if ( $type != $data['post_type'] ) return;
  // implemento una nuova azione "save" specifica per il posttype
  do_action('save_'.$type,$post_id,$post);
  //if ( !current_user_can( 'edit_'.$type, $post_id )) return;  //@FIXME : dovrei usare il plurale per creare la capability
  if (!isset($admin_post_data[$type])) return;
  foreach($admin_post_data[$type] as $boxname => $fields) {
    if ( wp_verify_nonce( @$_POST[$type.'_'.$boxname.'_noncename'], plugin_basename( __FILE__ ) ) || $verified ) {
	foreach($fields as $name => $field) {
		$tosave=true;
		switch ($field['type']) {
			case 'date':
				$value = date_create_from_format('d-m-Y',$data[$name]);
				if (!is_object($value)) $value = new DateTime();
				$value = $value->format('Y-m-d');
			break;
			case 'file':
				// mi calcolo comunque il percorso
				$uploaded = @$files[$name];
				$uploads = wp_upload_dir();
				$UPLOAD_REL = $field['position'];
				$UPLOAD_DIR = $uploads['basedir'].'/'.$UPLOAD_REL;
				$UPLOAD_URL = $uploads['baseurl'].'/'.$UPLOAD_REL;
				// se non esiste creo la cartella per la posizione
				if (!is_dir($UPLOAD_DIR)){
					mkdir($UPLOAD_DIR, 0777);
				}
				$fileslist = admin_scandirectory( $UPLOAD_DIR ); // vedo cosa c'� nella cartella
				//admin_debug($uploaded, 'file caricato',true);
				if (isset($data[$name.'_delete'])) {
					// devo eliminare il file
					$value = ''; // lo scollego al post-meta
					$args = array( 'post_type' => 'attachment', 'numberposts' => -1, 'post_status' => null, 'post_parent' => $post_id );
					$attachments = get_posts( $args );
					if ($attachments)
						foreach ( $attachments as $atta )
						if ($atta->guid==$old_file) wp_delete_attachment( $atta->post_id);
				} elseif($uploaded['error'] == UPLOAD_ERR_OK and is_uploaded_file($uploaded['tmp_name'])) {
					// è stato caricato un file
					//admin_debug('nessuno', 'Errore',true);
					$parent = $post_id;
					$filepart = admin_fileinformation( $uploaded['name'] );
					// creo il nome del file di destinazione
					$filebase = sanitize_file_name($field['pre-name']).sanitize_file_name($data['post_title']);
					$filename = $filebase.'.'.$filepart['extension'];
					// check if this filename already exist in the folder
					//admin_debug($filepart, 'informazioni',true);
					$i = 2;
					while ( in_array( $filename, $fileslist ) ) {
						$filename = $filebase . '_' . $i++ . '.' .$filepart['extension'];
					}
					$file =  $UPLOAD_DIR.'/'.$filename;
					$url  =  $UPLOAD_URL.'/'.$filename;
					$path =  $UPLOAD_REL.'/'.$filename;
					move_uploaded_file($uploaded["tmp_name"], $file);
				
					// creiamo il media-post
					$wp_filetype = wp_check_filetype(basename($file), null );
					$attachment = array(
							'post_mime_type' => $wp_filetype['type'],
							'post_title' => $data['post_title'],
							'post_name' => preg_replace('/\.[^.]+$/', '', basename($file)),
							'post_content' => $data['post_content'],
							'post_excerpt' => $data['post_excerpt'],
							'post_status' => 'inherit',
							'post_parent' => $post->ID,
							'guid' => $url
					);
					 
					$attach_id = wp_insert_attachment( $attachment, $url, $parent );
					// aggiorniamo i metadata
					set_postmeta($attach_id,'_wp_attached_file', $path);
					set_postmeta($attach_id,'_wp_attachment_metadata', 'a:0:{}');
					$value = $url;
					//admin_debug($url, 'url',true);
				} else $value = get_post_meta($post_id,$name,true);
			break;
			case 'parent':
				$new_post = array('ID' => $post_id, 'post_parent' => $data[$name]);
				remove_action('save_post', 'ah_post_save',5,2);
				wp_update_post($new_post);
				add_action('save_post', 'ah_post_save',5,2);
				$tosave=false;
			break;
			default:
				$value = $data[$name];
			break;
				
		}
        // in ogni caso salva il postmeta
	    if ($tosave) set_postmeta($post_id,$name,$value);
	} // foreach $fields
    } // if $verified
  } // foreach $boxex per il $type
   
}


// aggiunge il contenuto della colonna nella tabella
function ah_list_column_row( $column_name, $id ) {
global $admin_post_data_value;
global $admin_post_data;
global $current_screen;

	
//	$boxes = $admin_post_data[$posttype];
	switch ($column_name) {
		case 'post_title':
		case 'title' : 
			echo get_post($id)->post_title;
		break;
		case 'post_author':
			echo get_post($id)->post_author;
		break;
		case 'post_status':
			echo get_post($id)->post_status;
		break;
		default:
			echo get_post_meta($id,$column_name,true);			
		break;
	}

/*
	$posttype = $current_screen->post_type;
	$boxes = $admin_post_data[$posttype];
	if ($boxes) foreach ($boxes as $name => $field)
			if ($name==$column_name) {
					// devo ritornare qualcosa
					//in genere :
					return get_post_meta($post_id,$column_name,true);
					//@TODO va vompletato quello che ritorno
					// ma potrebbe essere anche una select e quindi devo restituire il valore associato, 
					// o anche parent o status o una tassonomia, ma non � qui che va
			}
*/
}

// aggiunge le colonne della tabella
function ah_list_column_header( $cols ) {
global $admin_post_data_value;
global $admin_post_data;
	
global $current_screen;

	$posttype = $current_screen->post_type;
	$boxes = $admin_post_data[$posttype];
	if ($boxes!='') foreach ($boxes as $name => $fields) 
						foreach ($fields as $name => $field)
							if ($field['intable']==true) 
										$cols[$name]=$field['description'];
	return $cols;
}

// indica che la colonna è ordinabile
function ah_list_column_sort($cols) {
global $admin_post_data_value;
global $current_screen;
	
	$posttype = $current_screen->post_type;

	$boxes = $admin_post_data_value[$posttype];
	if ($boxes) foreach ($boxes as $name => $fields)
					foreach ($fields as $name => $field)
						if ($field['orderable']==true) 
										$cols[$name]=true;
	return $cols;
}

function ah_list_filter() {
global $current_screen;
		//@TODO questo va sistemato
		
		$posttype = $current_screen->post_type;

// devo scorrere i box ed i field per vedere se qualcuno ha filterable

// devo cercare i valori di quel campo all'interno della tabella

// stampo la select : ricorda che il campo filtrato me lo ritrovo in get

}

function ah_current_screen($cs) {
	$posttype=$cs->post_type;
	add_filter('manage_'.$posttype.'_posts_columns','ah_list_column_header');
	add_action('manage_'.$posttype.'_posts_custom_column',  'ah_list_column_row', 10, 2 );
	add_filter('manage_edit-'.$posttype.'_sortable_columns', 'ah_list_column_sort' );
	
}

function ah_posttype_hooks() {
    
    add_action('admin_init','ah_add_boxes');
    add_action('plugins_loaded','ah_posttype_loaded');
    add_action('save_post','ah_post_save',5, 2);
    add_filter('wp_insert_post_data','ah_post_insert',null,2);
    add_filter("the_post", "ah_post_get", 5, 2);
    add_action('activated_plugin', 'ah_posttype_first');
    
    add_action('restrict_manage_posts', 'ah_list_filter');
    add_action('current_screen','ah_current_screen');
    

 }



ah_posttype_hooks();

?>
