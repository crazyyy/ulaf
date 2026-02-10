<?php
/*
Plugin Name: WP Helper - debug
Description: Some function to help developer to debug (integrate with admin debug bar)  
Plugin URI:  http://www.decristofano.it/
Version:     0.9
Author:      lucdecri
Author URI:  http://www.decristofano.it/
*/

define('WP_HELPER_DEBUG','0.9');

require_once ('helper_basic.php');

function admin_debug($var,$name='', $permanent=false) {
    global $admin_debug_data;
	if (function_exists('dbgx_trace_var')) {
		if ($name=='') dbgx_trace_var( $var );
		else		dbgx_trace_var( $var,$name );
	} else {
	    $bt = debug_backtrace();
	    $refer = $bt[0]['file']."@".$bt[0]['line'];
	    $string = print_r($var,true);
	    $admin_debug_data[]=array(
		    'name' => $name, "var" => $string, "type" => 'info', "time" => microtime(true), 'refer' => $refer
		    );
	}
        if ($permanent) {
            $md = get_option('admin_debug', array());
            $md[]=array($name,$var); 
            update_option('admin_debug', $md);
        }
}

function admin_debug_reset() {
    delete_option('admin_debug');
}




/* ******************** 
 * for internal use   *
 * ******************** */



function ah_debug_loaded() {
    global $admin_debug_data;

    	$admin_debug_data = array();

    
}

function ah_prefooter() {
global $admin_post_data;
global $admin_boxes_data;
global $admin_taxonomy_data;
global $admin_post_data_value;
global $menu;
global $submenu;


    // debug delle variabili globali
    admin_debug($admin_post_data,'admin_post_data');
    admin_debug($admin_post_data_value,'admin_post_data_value');
    admin_debug($admin_boxes_data,'admin_boxes_data');
    admin_debug($admin_taxonomy_data,'admin_taxonomy_data');
    admin_debug(get_option('admin_debug','-'),'debug permanent');

    admin_debug($menu,'WP MENU');
    admin_debug($submenu,'WP SUBMENU');
}

function ah_footer() {
global $admin_debug_data;

// carico i js che mi potrebbero servire

    echo '<script type="text/javascript">
	function ChangeColor(id,color) {
		jQuery(id).css("background-color","#"+color);
	}
	jQuery(document).ready(function(){
                jQuery(".datepicker").datepicker({ dateFormat: "dd-mm-yy" });
	});
        tinyMCE.init({
            mode : "textareas",
            language : "en"
        });
	</script>';

// stampa il debug, se non non ho altro
    if (count($admin_debug_data)==0) return '';
    $debug='';
    $debug.= '
	    <style>
			.debug_wrapper {
				width:80%;
				background:black;
				color : darkgray;
			}
			.debug_name {
				font-weight:bold;
				width : 60px;
			}
			.debug_time {
				font-style : italic;
			}
			.debug_log {
				color : blue;
			}
			.debug_message {
				color : yellow;
			}
			.debug_extend {
				background : #555555;
			}
			.debug_error {
				color : red;
			}
			.debug_warning {
				color : #FF7F00;
			}
			.debug_notice {
				color : #FFCC66;
			}
			
			.debug_strict {
				color : #FFCC66;
			}

			.debug_action {
				color : #9966FF;
			}
			
			.debug_filter {
				color : #6666CC;
			}
		
	    </style>
    ';
    $debug.= "<p class='debug_wrapper'>";
    foreach($admin_debug_data as $k => $line) {
			$data = htmlentities(print_r($line['var'],true));
			if (strlen($data)>100) $abstract=substr($data,0,100)." ...";
			else $abstract = $data;
			$data = nl2br("\n&nbsp;&nbsp;".$data);
			$debug.= "<span class='debug_{$line['type']}'><span class='debug_name'>{$k}:{$line['name']}</span>";
			$debug.= " @ ";
			$debug.= "<span class='debug_time'>".date('d-m-y H:i:s',$line['time'])."</span>";
			$debug.= "<span class='debug_view_refer'> # </span>";
			$debug.= "<span class='debug_refer'>".@$line['refer']." <br>&nbsp;&nbsp;&nbsp;</span>";
			$debug.= " &gt; ";
			$debug.= "<span class='debug_data'>$abstract</span>";
			$debug.= "<span class='debug_extend'>$data</span>";
			$debug.= "</span>";
			$debug.= '</span></br>';
	}
	$debug.= "</p>";
	$debug.= '
	<script>
	jQuery(document).ready(function() {
			jQuery(".debug_extend").hide();
			jQuery(".debug_refer").hide();
			
			jQuery(".debug_extend").click(function() {
				jQuery(this).toggle();
				jQuery(this).prev().toggle();
			});
			jQuery(".debug_view_refer").click(function() {
				jQuery(this).next().toggle();
			});
			jQuery(".debug_data").click(function() {
				jQuery(this).toggle();
				jQuery(this).next().toggle();
			});
	}); 
	</script>
	';
	// cancello cosi' ogni cosa la vedo una sola volta
	
    return $debug;
}


function ah_error($errno, $errstr, $errfile, $errline) {
global $admin_debug_data;
	switch ($errno) {
    case E_USER_ERROR:
    case E_ERROR:
        	$type='error';
        break;
    case E_USER_WARNING:
    case E_WARNING:
        	$type='warning';
        break;
    case E_USER_NOTICE:
    case E_NOTICE:
        	$type='notice';
        break;
	case E_DEPRECATED:
        	$type='deprecated';
    break;    
    case E_STRICT:
    		$type='strict';
    break;
    default:
        	$type='unknow:'.$errno;
        break;
    }
	$admin_debug_data[] = array(
		'name' => 'error', "var" => $errstr, "type" => $type, "time" => microtime(true), "refer" => $errfile."@".$errline
	); 
   return true;
}

function ah_debug_init() {
    if (!function_exists('dbgx_trace_var')) {
	    set_error_handler("ah_error");
    }
}

function ah_debug_hooks() {
    add_filter('wp_footer','ah_footer');  
    add_filter('admin_footer','ah_footer');
    add_action('wp_before_admin_bar_render','ah_prefooter');
    add_action('plugins_loaded','ah_debug_loaded');
    add_action('init','ah_debug_init');
    

}

ah_debug_hooks();

?>
