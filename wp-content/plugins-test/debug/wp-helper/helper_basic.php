<?php
/*
Description: basic function function to helper plugin
Plugin URI:  http://www.decristofano.it/
Version:     0.9
Author:      lucdecri
Author URI:  http://www.decristofano.it/
*/

define('WP_HELPER','0.9');

// ritorna i files in una cartella
function admin_scandirectory( $dirname = '.' ) { 
		$files = array(); 
		if( $handle = opendir( $dirname ) ) { 
			while( false !== ( $file = readdir( $handle ) ) ) {
				$info = pathinfo( $file );
                if ( isset($info['extension']) )
					   $files[] = utf8_encode( $file );
			}		
			closedir( $handle ); 
		} 
		sort( $files );
		return ( $files ); 
} 

function admin_ArrayFiles(&$file_post) {

    $file_ary = array();
    $file_count = count($file_post['name']);
    $file_keys = array_keys($file_post);

    for ($i=0; $i<$file_count; $i++) {
        foreach ($file_keys as $key) {
            $file_ary[$i][$key] = $file_post[$key][$i];
        }
    }

    return $file_ary;
}


function admin_fileinformation( $name ) {
		
		//Sanitizes a filename replacing whitespace with dashes
		$name = sanitize_file_name($name);
		
		//get the parts of the name
		$filepart = pathinfo ( strtolower($name) );
		
		if ( empty($filepart) )
			return false;
		
		// required until PHP 5.2.0
		if ( empty($filepart['filename']) ) 
			$filepart['filename'] = substr($filepart['basename'],0 ,strlen($filepart['basename']) - (strlen($filepart['extension']) + 1) );
		
		$filepart['filename'] = sanitize_title_with_dashes( $filepart['filename'] );
		
		//extension jpeg will not be recognized by the slideshow, so we rename it
		$filepart['extension'] = ($filepart['extension'] == 'jpeg') ? 'jpg' : $filepart['extension'];
		
		//combine the new file name
		$filepart['basename'] = $filepart['filename'] . '.' . $filepart['extension'];
		
		return $filepart;
}




// e' true se la pagina ha l'url specificato
function admin_check_url($url='') {
	if (!$url) return;
	
	$_REQUEST_URI = explode('?', $_SERVER['REQUEST_URI']);
	$url_len 	= strlen($url);
	$url_offset = $url_len * -1;
	// If out test string ($url) is longer than the page URL. skip
	if (strlen($_REQUEST_URI[0]) < $url_len) return;
	if ($url == substr($_REQUEST_URI[0], $url_offset, $url_len))
			return true;
}


function ah_form_field($id,$options, $mode='form', $localization = '') {
/* add a field in for 
* 	   $option is array with:
* 			type         => indica il tipo di campo. vedi admin_field
* 			hidden       => se true, il campo e' nascosto a prescindere dal type
* 			readonly     => se true, il campo e' in sola lettura a prescindere dal type
* 			default      => indica il valore da assegnare al campo nel caso non ve ne sia uno memorizzato
* 			option       => indica il valore da assegnare al campo opzionale nel caso non ve ne sia uno memorizzato
* 			values       => indica i valori tra cui vengono scelte le opzioni (nel caso, ad esempio di una select)
* 			label		 => il testo di descrizione del campo
* 			howto        => il testo di spiegazione
* 			closer       => un testo subito dopo l'input @TODO closer option
* 			automation   => va inserito nel tag dell'input @TODO automation option
* 			intable      => se true, il campo è visibile anche nella tabella riassuntiva dei posttype
* 			orderable    => se true e se intable è true, è ordinabile nella tabella riassuntiva dei posttype
* 			filterable   => se true e se intable è true, è presente una select per filtrare nella tabella riassuntiva dei posttype

*/
    $inputs = array(
      'break' => '<p> </p>',
      'text' => '<input type="text" id="%1" name="%1" value="%2"  maxlength="300"  style="width:150px;" %disable >%3',
      'shorttext' => '<input type="text" id="%1" name="%1" value="%2"  maxlength="11"  style="width:100px;" %disable >%3',
      'smalltext' => '<input type="text" id="%1" name="%1" value="%2"  maxlength="5"  style="width:50px;" %disable >%3',
      'longtext' => '<br /><textarea  name="%1" id="%1" cols="60" rows="5" style="width:99%">%2</textarea %disable >',
      'formattedtext' => '<br /><textarea  name="%1" id="%1" cols="60" rows="5" style="width:99%">%2</textarea %disable ><SCRIPT LANGUAGE="javascript">cpf_add_mceEditor_javascript("%1");</SCRIPT>',
      'editor' => '<textarea  name="%1" id="%1" cols="60" rows="5" style="width:99%" class="form-input-tip code" size="20" autocomplete="off" %disable >$3</textarea>',
      'number' => '<input type="text" id="%1" name="%1" value="%2"  maxlength="6"  style="width:100px;" class="validate_number" %disable>%3',
      'littlenumber' => '<input type="text" id="%1" name="%1" value="%2"  maxlength="3"  style="width:50px;" class="validate_number" %disable>%3',
      'color' => '#<input type="text" id="%1" maxlength="6" name="%1" value="%2" size="6" onchange="ChangeColor(\'%1_box\',this.value)" onkeyup="ChangeColor(\'%1_box\',this.value)" %disable /><span id="%1_box" style="background:#%2">&nbsp;&nbsp;&nbsp;</span>',
      'date' => '<input type="text" name="%1" id="%1" value="%2" class="Datepicker" style="width:100px;" %disable />',
      'file' => '<div class="file-input"><input id="%1" class="async-upload" type="file" name="%1" %disable /></div>', 
      'file_up' => '&nbsp;<a href="%2">%2</a> <input type="checkbox" name="%1_delete" id="$id" %disable />'.__('Delete'),
      'files' => '<div class="new-files"><div class="file-input"><input id="%1[]" class="async-upload" type="file" name="%1[]" /></div>
		    <a class="admin-add-file" href="javascript:void(0)">' . _('Add more files') . '</a></div>',
      'select' => '<select class="field_select" id="%1" name="%1" %disable >%2</select>',
      'parent' => '<select class="field_select" id="%1" name="%1" %disable >%2</select>',
      'select&text' => '<select class="field_select" id="%1" name="%1" %disable >%2</select><input type="text" maxlength="300" name="%1_option" id="%1_option" value="%4" style="width:150px;"  />%3',
      'number' => '<input type="text" id="%1" name="%1" value="%2" %disable>', 
      'readonly' => '<input type="hidden" name="%1" id="%1" value="%2" /><span class="readonly">&nbsp;%2&nbsp;</span>%3<br />',
      'hidden'   => '<input type="hidden" name="%1" id="%1" value="%2" />',
      'button'   => '<input type="button" name="%1" id="%1" value="%2" %disable />',
      'checkbox' => '<input type="checkbox" name="%1" id="%1" %2 %disable />',
      'noinput'  => ''
    );
    
    $std_options = array(
    				'type'			=> 'text',
    				'label'		 	=> 'field name',
    				'howto'			=> '',
    				'default'		=> '',
    				'values'  		=> '',
    				'conclusion' 	=> '', 
    				'default2'   	=> '',
    				'hidden'      	=> false,
    				'readonly'    	=> false,
    				'option'	 	=> '',
    				'intable'      => false,
    				'orderable'    => false,
    				'filterable'   => false,
    				'automation'  => ''	
    );
    
    $options = array_merge($std_options, $options);
    
    // definisce la label 
    if (($options['type']=='break') || ($options['type']=='hidden')) $label='';
    elseif ($options['type']=='files') '<label for="%1[]" class="label">%2</label>';
    else $label = '<label for="%1[]" class="label">%2</label>';
    
    
    // sistema i valori di default
    if (($options['type']=='file') && ($options['default'])) $options['type']='file_up';
    
    if (($options['type']=='date') && ($options['default'])) {$d = date_create(); $options['default'] = $d->format('d-m-Y');}
    
    if (($options['type']=='checkbox') && ($options['default']=='1')) $options['default']=' checked="checked" ';
    
    if (($options['type']=='select') || ($options['type']=='select&text')) {
            $values = explode(",",$options['values']);
            $default ='';
            $selected='';
            foreach($values as $opt) {
				$v=$opt;$k=$opt;	
          		if (strpos($opt,':')) list($k,$v)=explode(':',$opt,2);
          		$v=trim($v);
          		$k=trim($k);
				if ($k=='') $k=$v;
				if ($options['default']==$k)	{ $d = ' selected="selected" '; $selected = $v; }
          		else							  $d = ' ';
          		$default.= "<option value='$k' $d >".__($v,$localization)."</option>";
            }
            $options['default'] = $default;    
    }
    
    // sistema il readonly
    //@TODO BUG : qui non funziona il readonly per il text di select&text
    if ((($options['type']=='select') || ($options['type']=='select&text')) && ($options['readonly'])) $options['readonly']=' disable="disable" ';
    elseif ($options['readonly']) $options['readonly']=' readonly="readonly" ';
    else $options['readonly'] = '';
    
             
    $label =str_replace(       array('%1'         ,'%2'             ), 
                               array($id          ,$options['label']  ),
                               $label);
    $help = '<p>'.__($options['howto'],$localization).'</p>';

    if ($options['hidden']) {
        $help='';
        $label='';
        $input= str_replace(   array('%1', '%2'               ), 
                               array($id , $options['default']),
                               '<input type="hidden" name="%1" id="%1" value="%2" />');
    } else {
        $input =str_replace(   array('%1'         ,'%2'              ,'%3'                   ,'%4'              ,'%disable'), 
                               array($id         ,$options['default'], $options['conclusion'],$options['option'], $options['readonly']),
                               $inputs[$options['type']]);
    }
    
    // visto che la select va in disabled, aggiungo un campo hidden, altrimenti non la ritrovo in get (ma poi mi serviva? se √® readonly dovrebbe gi√† essere settata, e quindi non serve che la cambio)
    if ((($options['type']=='select') || ($options['type']=='select&text')) && ($options['readonly'])) 
                $input.= str_replace(   array('%1', '%2'               ), 
                                        array($id , $selected),
                                        '<input type="hidden" name="%1" id="%1" value="%2" />'); //@FIXME %2 da problema perchè value ora sono le options
    
    
    
    if ($mode==='form') $string = '<div class="field_wrapper form-field">'.$label.$input.$help.'</div>';
    elseif ($mode==='table') $string = '<tr class="form-field"><th valign="top" scope="row">'.$label.'</th><td>'.$input.$help.'</td></tr>';
    elseif ($mode==='simple') $string = $input;
    
    return $string;
    
}


function admin_field($id, $type, $options, $default, $localization='', $message='', $return=false) {
	
	if (!is_array($options)) { 
		@list($text,$text2,$text3,$text4) = @explode('|',$options,4);
		$options = array(
				'label'		 	=> $text,
				'values'  		=> $text2,
				'conclusion' 	=> $text3, // non è 'closer' ?
				'default2'   	=> $text4,
				'hidden'      	=> (($type=='hidden')?true:false),
				'readonly'    	=> (($type=='readonly')?true:false),
				'option'	 	=> '',
				'intable'      => false,
				'orderable'    => false,
				'filterable'   => false,
				'automation'  => ''
		);
		
	}
	$options['type'] = $type;
	$options['default']=$default;
	$options['howto'] = $message;
	//@TODO da compilare option
	if($return) return ah_form_field($id, $options, 'form');
	else 		echo   ah_form_field($id,  $options, 'form');
	
}



function old_admin_field($id, $type, $options, $default, $localization='', $message='', $return=false ) {
  // add a field in form for admin panel
  //   type is the field type :
  //      littlenumber
  //      text
  //      color
  //      page : a page-break in the admin panel
  //      longtext
  //	  checkbox
  //	  hidden
  //	  button
  //	  readonly
  //	  date
  //	  file
  //	  files
  //      formattedtext
  //	  select, parent
	
	
    if (is_array($options)) {
        $text  = $options['description'];
        $text2 = $options['values'];
        $text3 = $options['conclusion'];
        $default2 = $options['default2'];
    } else {
        @list($text,$text2,$text3,$default2) = @explode('|',$options,4);
    }
    $string = '<div class="field_wrapper form-field">';
    switch($type) {
	case 'break': //x
	    $string.= '<p></p>';
	break;
        case 'array':
                $string.='';
        break;
        case 'color':
        	$string.= '
          <label for="'.$id.'" class="label">'.__($text,$localization).'</label>
          #<input type="text" id="'.$id.'" maxlength="6" name="'.$id.'" value="'.$default.'"
	      size="6" onchange="ChangeColor(\''.$id.'_box\',this.value)" onkeyup="ChangeColor(\''.$id.'_box\',this.value)"/>
	      <span id="'.$id.'_box" style="background:#'.$default.'">&nbsp;&nbsp;&nbsp;</span>
	      <p>'.__($message,$localization).'</p>';
        break;
	case 'date':
	       $default= date_create_from_format('Y-m-d',$default);
	       if(!$default) $default=date_create();
		$default= $default->format('d-m-Y');
		$string.= '
		<label for="'.$id.'" class="label">'.__($text,$localization).'</label>
		<input type="text" name="'.$id.'" id="'.$id.'" value="'.$default.'" class="Datepicker" style="width:100px;"/>
		<p>'.__($message,$localization).'</p>
		';
	break;
	case 'file':
	    $string.= '<label for="'.$id.'">'.__($text,$localization).'</label>';
            if ($default) $string.="&nbsp;<a href='$default'>$default</a> <input type='checkbox' name='{$id}_delete' id='$id' />".__('Delete',$localization);
            else $string.='<div class="file-input"><input id="'.$id.'" class="async-upload" type="file" name="'.$id.'" /></div>';
            $string.='<p>'.__($message,$localization).'</p>';
	break;
	case 'files':
	    $string.= '<label for="'.$id.'[]">'.__($text,$localization).'</label>
		<div class="new-files">
		    <div class="file-input"><input id="'.$id.'[]" class="async-upload" type="file" name="'.$id.'[]" /></div>
		    <a class="admin-add-file" href="javascript:void(0)">' . _('Add more files') . '</a>
		</div>
		<p>'.__($message,$localization).'</p>';
	    $string.= '<script type="text/javascript">
		jQuery(document).ready(function($) {
			// add more file
			$(".admin-add-file").click(function(){
				var $first = $(this).parent().find(".file-input:first");
				$first.clone().insertAfter($first).show();
				return false;
			});
		});
		</script>';
	break;

        case 'page':
		$string.= '
             	</div>
            	</fieldset>
	            <br />
                <fieldset>
	            <legend><b>'.__($text,$localization).'</b></legend>
	            <div class="Option">
                <p><i>'.__($message,$localization).'</i></p>';
        break;
        case 'select':
        case 'parent':  // qui sono identici, cambiano quando salvo
		$string.= '
		<label for="'.$id.'" class="label">'.__($text,$localization).'</label>
          <select class="field_select" id="'.$id.'" name="'.$id.'" >';
          	$options = explode(",",$text2);
          	foreach($options as $opt) {
				$v=$opt;$k=$opt;
				
          			if (strpos($opt,':')) list($k,$v)=explode(':',$opt,2);
				if ($k=='') $k=$v;
				
				if ($default==$k)	$d = ' selected="selected" ';
          			else			$d = ' ';
				
          			$string.= "<option value='$k' $d >".__($v,$localization)."</option>";
          	}
          $string.= '
          </select>
          <p>'.__($message,$localization).'</p>';
        break;
        case 'select&text':
		$string.= '
		<label for="'.$id.'" class="label">'.__($text,$localization).'</label>
          <select class="field_select" id="'.$id.'" name="'.$id.'" >';
          	$options = explode(",",$text2);
          	foreach($options as $opt) {
				$v=$opt;$k=$opt;
				
          			if (strpos($opt,':')) list($k,$v)=explode(':',$opt,2);
				if ($k=='') $k=$v;
				
				if ($default==$k)	$d = ' selected="selected" ';
          			else			$d = ' ';
				
          			$string.= "<option value='$k' $d >".__($v,$localization)."</option>";
          	}
          $string.= '
          </select>
          <input type="text" maxlength="300" name="'.$id.'_option" id="'.$id.'_option" value="'.$default2.'" style="width:150px;" />'.$text3.'<br />
          <p>'.__($message,$localization).'</p>';
        break;
        case 'littlenumber':
	case 'smallesttext':
	  $string.= '
          <label class="label" for="'.$id.'">'.__($text,$localization).'</label><input type="text" name="'.$id.'" value="'.$default.'" maxlength="5"  style="width:50px;" /> '.__($text2,$localization).'<br />
	   <p>'.__($message,$localization).'</p>';
        break;
        case 'smalltext':
	  $string.= '<label class="label" for="'.$id.'">'.__($text,$localization).'</label>
          <input type="text" maxlength="100" name="'.$id.'" id="'.$id.'" value="'.$default.'" style="width:100px;" /> '.__($text2,$localization).'<br />
	  <p>'.__($message,$localization).'</p>';
        break;
        case 'text':
          $string.= '
          <label class="label" for="'.$id.'">'.__($text,$localization).'</label>
          <input type="text" maxlength="300" name="'.$id.'" id="'.$id.'" value="'.$default.'"  />'.__($text2,$localization).'<br />
	  <p>'.__($message,$localization).'</p>';
        break;
        case 'hidden':
          $string.= '<input type="hidden" name="'.$id.'" id="'.$id.'" value="'.$default.'" />';
        break;
        case 'button':
        	// @TODO button non funziona
           $string.= '<input type="button" name="'.$id.'" id="'.$id.'" value="'.$text.'" />';
        break;
        case 'longtext':
		if ($text!='') 
			$string.= '<label for="'.$id.'" class="label">'.__($text,$localization).'</label><br />';
		$string.= '<textarea  name="'.$id.'" id="'.$id.'" cols="60" rows="5" style="width:99%">'.$default.'</textarea> '.__($text2,$localization).'<br />
			<p>'.__($message,$localization).'</p>';
        break;
        case 'formattedtext':
		if ($text!='') 
			$string.= '<label for="'.$id.'" class="label">'.__($text,$localization).'</label><br />';
		
                $string.= '<textarea  name="'.$id.'" id="'.$id.'" cols="60" rows="5" style="width:99%" class="additional-info form-input-tip code" size="20" autocomplete="off" >'.$default.'</textarea> '.__($text2,$localization).'<br />
			<p>'.__($message,$localization).'</p>
					<SCRIPT LANGUAGE="javascript">cpf_add_mceEditor_javascript("'.$id.'");</SCRIPT>';
        break;
       
       case 'readonly':
	  $size = strlen($default);  
          $string.= '
          <label class="label" for="'.$id.'">'.__($text,$localization).'</label>
	  <input type="hidden" name="'.$id.'" id="'.$id.'" value="'.$default.'" />
	  <span style="color:darkgray; font-family: Consolas,Monaco,monospace; font-style: italic;border: solid 1px; background: none repeat scroll 0 0 #EAEAEA;">&nbsp;'.$default.'&nbsp;</span>'.__($text2,$localization).'<br />
          <p>'.__($message,$localization).'</p>';
        break;
        case 'checkbox':
		$string.= '<label for="'.$id.'" class="label">'.__($text,$localization).'</label><input type="checkbox" name="'.$id.'" id="'.$id.'" ';
		if($default == '1') { $string.= ' checked="checked" '; }
		$string.= ' /><br /><p>'.__($message,$localization).'</p>';
        break;
    }
    $string.= '</div>';
    
    if ($return) return $string;
    else	 echo   $string;
}


function admin_multifield($id,$args, $values, $clousure = array()) {
// aggiunge un campo multiplo composto da pi√π campi
    global $post;
    
    $all_form = array(
      'text' => '<input type="text" id="%1" name="%1" value="%2">',
      'number' => '<input type="text" id="%1" name="%1" value="%2">', 
      'readonly' => ''
    );
    
    echo "<br>VALUES : <pre>";
    print_r($values);
    echo "</pre>";
    $string = '<table class="wp-list-table widefat fixed" cellspacing="0"><thead><tr>';
    $cols=1;
    // prima di tutto stampo una riga di intestazione
    foreach($args as $name => $field) {
            $string.='<th scope="col">'.$field['label'].'</th>';
            $cols++;
    }
    $string.='<th>Delete</th></tr><thead><tbody>';
    if (isset($post->ID)) $post_type = $post->post_type;
    else $post_type='';
    // poi riempio la tabella
    foreach($values as $k=>$row) {
        
        $string .= '<tr>';
        foreach($args as $name => $field) {
            $string.='<td>'.apply_filters($post_type.'_field', @$row[$name], $name).'</td>';
        }
        $string.="<td><input type='checkbox' id='".$id.'__delete['.$k."]' name='".$id.'__delete['.$k."]' /></td>";
        $string.="</tr>\n";   
    }
    
    // infine lascio una riga vuota
    $string.="</tbody><tfoot><tr>";
    foreach($args as $name => $field) {
            $string.='<th scope="col">';
            $string.=str_replace(
                                array('%1'         ,'%2'             ,'%3'), 
                                array($id.'_'.$name,$field['default'], $field['label']),
                                $all_form[$field['type']]);
            $string.='</th>';
    }
    $string.='<th><input type="submit" name="save-post" value="Salva" id="aggiungi" class="button"></th></tr>';
    if ($clousure!=array()) {
        foreach($clousure as $name=>$field) {
            $string .= '<tr >';
            $string.='<td colspan="'.$cols.'" style="text-align:right; width:100%;">'.apply_filters($post_type.'_field', $field['default'], $name).'</td>';
            $string.="</tr>\n";
            } 
    }
    $string.='</tfoot></table>';
    echo $string;
    
}

function execute_thisplugin_first($plugin_file) {
	// ensure path to this file is via main wp plugin path
	$wp_path_to_this_file = preg_replace('/(.*)plugins\/(.*)$/', WP_PLUGIN_DIR."/$2", $plugin_file);
	$this_plugin = plugin_basename(trim($wp_path_to_this_file));
	$active_plugins = get_option('active_plugins');
	$this_plugin_key = array_search($this_plugin, $active_plugins);
	if ($this_plugin_key) { // if it's 0 it's the first plugin already, no need to continue
		array_splice($active_plugins, $this_plugin_key, 1);
		array_unshift($active_plugins, $this_plugin);
		update_option('active_plugins', $active_plugins);
	}
}




/* ******************** 
 * for internal use   *
 * ******************** */




function ah_init() {
	wp_register_style('wp_helper_css', plugins_url( 'helper.css' , __FILE__ ));
	wp_enqueue_style('wp_helper_css');

	
}

function ah_load_script() {
    wp_register_script('helper_js', plugins_url( 'helper.js' , __FILE__ ));
    wp_register_script('jquery-style','http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');  
    wp_register_script('tiny_mce.js',get_bloginfo('url').'/wp-includes/js/tinymce/langs/wp-langs-en.js');
    wp_register_script('wp-langs-en.js',get_bloginfo('url').'/wp-includes/js/tinymce/langs/wp-langs-en.js');
    wp_register_script('wp-langs-it.js',get_bloginfo('url').'/wp-includes/js/tinymce/langs/wp-langs-it.js');
    
    wp_enqueue_script('jquery');
    wp_enqueue_style('jquery-style');
    wp_enqueue_script('helper_js');
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_script('tiny_mce.js');
    wp_enqueue_script('wp-langs-en.js');
    wp_enqueue_script('wp-langs-it.js'); 
    
   wp_enqueue_style('jquery-style','http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
 }
 
function ah_edit_form_tag() {
    echo ' enctype="multipart/form-data"';
}


function ah_basic_hooks() {
    
    add_action('admin_init','ah_init');
//    add_filter('wp_footer','ah_footer');  
//    add_filter('admin_footer','ah_footer');
    add_action('admin_enqueue_scripts','ah_load_script');
//    add_action('plugins_loaded','ah_loaded');

//    add_action('save_post','ah_post_save',5, 2);
//    add_filter('wp_insert_post_data','ah_post_insert',null,2);
//    add_filter("the_post", "ah_post_get", 5, 2);
//    add_filter("attachment_fields_to_edit", "ah_attachment_fields_to_edit", null, 2);
//    add_filter("attachment_fields_to_save", "ah_attachment_fields_to_save", null, 2);
//    add_filter('get_the_terms','ah_taxonomy_get_terms',1,3);
    add_action('post_edit_form_tag', 'ah_edit_form_tag');
    
}

ah_basic_hooks();

?>
