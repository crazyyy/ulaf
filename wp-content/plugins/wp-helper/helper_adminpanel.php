<?php
/*
Plugin Name: WP Helper - admin panel
Description: Some function to help developer to create admin panels  
Plugin URI:  http://www.decristofano.it/
Version:     0.9
Author:      lucdecri
Author URI:  http://www.decristofano.it/
*/

define('WP_HELPER_PANEL','0.85');

require_once ('helper_basic.php');

// inserisce un divisore di menu' dopo il menu indicato 
function add_menu_div($after) {
global $menu;
    $id = 'separator'.rand(100,1000);
    $div = array (
    0 => '',
    1 => 'read',
    2 => $id,
    3 => '',
    4 => 'wp-menu-separator',
    5 => $id
    );
    $appendto=max(array_keys($menu));
    $menu[$appendto+10] = $div;
    admin_move_menu($id, $after);
    
}    


function admin_move_menu($menutomove, $menumoveafter) {
global $menu;
    
    $menumoveto=max(array_keys($menu));
    $menutomove_data=array();
    $menutomove_id=-1;
    $count = 0;
    // cerco il menu da spostare e l'id della nuova posizione
    foreach ($menu as $menuid => $menudata) {
            $count++;
            if (@$menudata[5]==$menutomove)      {$menutomove_data = $menudata;  $menutomove_id=$menuid;}
            if (@$menudata[5]==$menumoveafter)   {$menumoveto_id = $menuid+1;    $position=$count; }
    }
    // non ho trovato il menu da spostare
    if ($menutomove_data===array()) return;
    if ($menutomove_id==-1) return;
    // qui ho trovato cosa spostare
    
    // lo tolgo dalla posizione attuale
    unset($menu[$menutomove_id]);
    // se ho gia' lo spazio libero lo posiziono
    if (!isset($menu[$menumoveto_id])) $menu[$menumoveto_id] = $menutomove_data; 
    else {  // altrimenti devo farmi spazio
        $menu_a = array_slice($menu, 0,$position,true);
        $menu_b = array_slice($menu,$position); //fino alla fine
        
        $menu = array_merge($menu_a, array($menumoveto_id=>$menutomove_data),$menu_b);
    }
    ksort($menu);


}


function set_option($name, $val=null) {
// save option named $name, if $val is defined
	$old = get_option($name);
	if ($val!=null) {
		if (get_option($name,'')!='') {
			update_option($name,$val);
		} else {
			add_option($name,$val,'',true);
		}
	}
}




// produce una tabella di visualizzazione dei post
// come parametri accetta l'array di query_post.
// non modifica il main-loop
function admin_table_posts($options, $columns, $query_array) {
       // definisco l'header della tabella
    
    $postsTable = new ah_Posts_Table($options);
    $postsTable->set_culumns($columns);
    $postsTable->set_ordinable_columns(@$options['order']);
    $postsTable->set_actions(@$options['actions']);
    // cerco i post da visualizzare e li metto in $posts
    $posts = get_posts($query_array); 
    
    //Fetch, prepare, sort, and filter our data...
    $postsTable->prepare_items($posts);
    $postsTable->display();
    
}

function admin_menu($parent, $page_title, $menu_title, $function_name, $menu_slug, $position='', $capability='edit_plugins', $icon_url='') {
	// add a panel in wordpres menu
	
	
	switch ($parent) {
		case '' : $parent = ''; break;
		case 'Dashboard': $parent='index.php'; break;
		case 'Posts': $parent='edit.php'; break;
		case 'Media': $parent='upload.php'; break;
		case 'Links': $parent='link-manager.php'; break;
		case 'Pages': $parent='edit.php?post_type=page'; break;
		case 'Comments': $parent='edit-comments.php'; break;
		case 'Appearance': $parent='themes.php'; break;
		case 'Plugins': $parent='plugins.php'; break;
		case 'Users': $parent='users.php'; break;
		case 'Tools': $parent='tools.php'; break;
		case 'Settings': $parent='options-general.php'; break;
	}
	
	if ($parent=='') 
		add_menu_page   (          $page_title, $menu_title, $capability, $menu_slug, $function_name, $icon_url, $position );
	else
		add_submenu_page( $parent, $page_title, $menu_title, $capability, $menu_slug, $function_name ); 
}

function admin_panel($name, $action, $title, $description, $info='') {
  // create a form for admin panel
    echo '<div class="wrap helper_panel">
    		<h2>'.$title.'</h2>
    		<h5>'.$info.'</h5>
    		<p>'.$description.'</p>
			<p><form name="'. $name.'" action="'.$action.'" method="post" id="'.$name.'">
			<fieldset>
    			<div class="UserOption">
   					<input type="hidden" name="page" value="'.$name.'" />';
}

function admin_panel_close() {
  // close a form of admin panel
  echo '
    </fieldset>
    '.submit_button().'	
    </form>
    </p>
</div>
  ';
}






/* ******************** 
 * for internal use   *
 * ******************** */



// classe per gestire le tabelle di visualizzazione dei posttype 


if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class ah_posts_Table extends WP_List_Table {
    
   var $_cols;
   var $_ords;
   var $_acts;
    
    function __construct($options ){
        global $status, $page;
                
        $default = array (
            'singular'  => 'post',
            'plural'    => 'posts',
            'evidence'  => '',
            'comment'   => '',
            'ajax'      => false,
            'actions'   => array('edit'=> 'Edit')
        );
        $options = array_merge($default, $options);
        //Set parent defaults
        parent::__construct( $options);
        
    }
    
    
    function column_default($item, $column_name){
        //@TODO controllare meglio se la colonna esiste
       $my = get_post($item['ID']);
       return apply_filters('manage_'.$my->post_type.'_posts_custom_column', $column_name,$my->ID);
        
    }
    
    // nel caso del titolo richiamo una funzione diversa, visto che ho da aggiungere le azioni
    function column_title($item){
        
        //Build row actions
        $post = @$_REQUEST['post'];
        if ($post=='') $post = @$_REQUEST['page'];
        if ($post=='') $post = @$_REQUEST['ID'];
        foreach ($this->_acts as $name => $label) 
            $actions[$name] =  sprintf('<a href="?post=%s&action=%s&from=%s&position=%s">%s</a>',$item['ID'],$name,$post,$item['position'],$label);
        
        
        //Return the title contents
        return sprintf('<b>%s</b> %s <span style="color:silver">%s</span>%s',
                     @$item[$this->_args['evidence']],
            /*$1%s*/ $item['title'],
            /*$2%s*/ @$item[$this->_args['comment']],
            /*$3%s*/ $this->row_actions($actions)
        );
    }
    
   // va lasciato cosi'
   function column_cb($item){
       admin_debug($item, 'Dati passati');
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label 
            /*$2%s*/ $item['ID']                //The value of the checkbox should be the record's id
        );
    }
    
    function set_culumns($cols = array()) {
	if ($cols==array()) $cols = array('cb' => '<input type="checkbox" />', 'title'     => 'Title');
	$this->_cols=$cols;
    }
    
    function set_ordinable_columns($ords = array()) {
	$this->_ords = $ords;
    }
    
    function get_columns(){
        return $this->_cols;
    }
    
    function get_sortable_columns() {
        return $this->_cols;
    }
    
    function set_actions($act = array() ) {
	$this->_acts = $act;
    }
     
    function get_bulk_actions() {
        return $this->_acts;
    }
    
    function display_tablenav( $which ) {
    // gestisco anche questo perchè devo togliere in nonce_field e bulk_actions, 
    // altrimenti mi sballano la pagina quando salvo
    
		//if ( 'top' == $which )
		//	wp_nonce_field( 'bulk-' . $this->_args['plural'] );
        if ( 'bottom' == $which) return;
?>
    
	<div class="tablenav <?php echo esc_attr( $which ); ?>">

		<div class="alignleft actions">
			<?php //$this->bulk_actions( $which ); ?>
		</div>
<?php
		$this->extra_tablenav( $which );
		$this->pagination( $which );
?>

		<br class="clear" />
	</div>
                 
<?php
                 
	}

    
    function prepare_items($posts) {
        
        $per_page = 5;
        

        $columns = $this->get_columns();
        $hidden = array(); //array('ID' => true);
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        // creo un hook per gestire le azioni
        if (isset($_REQUEST['page'])) do_action('do_action', $this->current_action(), $_REQUEST['page'], $posts[$_REQUEST['position']]);

        $data=array();
        // riempio il file data, leggendo dai post
		foreach($posts as $id => $post) {
                foreach($this->_column_headers[0] as $col => $name)
                                if (isset($post->$col))     $data[$id][$col] = $post->$col;
                                else                            $data[$id][$col] = '-';
                $data[$id]['title'] = $post->post_title;
                $data[$id]['ID'] = $post->ID;
                $data[$id]['position'] = $id;
        }
        //immagino che i post siano gia' ordinati...
        //@TODO da verificare l'ordinamento degli array
        
        
        // preparo la paginazione
        $current_page = $this->get_pagenum();
        $total_items = count($data);
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        
        
        $this->items = $data;
        
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }
    
}


function ah_adminpanel_hooks() {
    
 }

ah_adminpanel_hooks();

?>
