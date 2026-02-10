<?php
/*
 * Plugin Name:		Find Slow Functions & Actions & Filters & Hooks
 * Description:		Find out slow functions, actions or filters with their exact times in seconds or milliseconds. (Note: plugin automatically sets/writes the needed variable in wp-config).
 * Text Domain:		debug-functions-time
 * Domain Path:		/languages
 * Version:		1.44
 * WordPress URI:	https://wordpress.org/plugins/debug-functions-time/
 * Plugin URI:		https://puvox.software/software/wordpress-plugins/?plugin=debug-functions-time
 * Contributors: 	puvoxsoftware,ttodua
 * Author:		Puvox.software
 * Author URI:		https://puvox.software/
 * Donate Link:		https://paypal.me/Puvox
 * License:		GPL-3.0
 * License URI:		https://www.gnu.org/licenses/gpl-3.0.html
 
 * @copyright:		Puvox.software
*/


 

// stand-alone namespace.

namespace DebugFunctionsTimeMain
{

  class TestTimes
  {

	private $standard_way = false; //true (standard "all_actions" hook) or false (my way)
	private $allow_flag=false;
	private $ip;
	private $actions= [];

	// https://pantheon.io/blog/tracing-wordpress-actions-and-filters
	public function __construct()
	{
		$ip = $_SERVER['REMOTE_ADDR'];
		if (is_admin())	{  add_action('init', [$this, 'enable_display_set']);  }
		
		if ($this->enabled_display())
		{
			if ($this->standard_way) add_action( 'all',		[$this, 'all_action'],  -1); //priority doesnt matter with this
		}
		
		//$this->excludeActions =['gettext','gettext_with_context'];	// Some actions are not going to be of interest to you. 

		// only when normal footer happens, assume that is ok to display info.
		add_action('wp_head', 		[$this, 'footer_enable_flag']);
		add_action('admin_head',	[$this, 'footer_enable_flag']);
		register_shutdown_function( [$this, 'shutdown'] );
	}


	// #######################   standard way   #######################//
	public function parent_type() {
		$traces = debug_backtrace();
		for($i=0; $i<count($traces)-1; $i++)
		{
			if ($traces[$i]["function"] == "apply_filters")  return "apply_filters";
			if ($traces[$i]["function"] == "do_action")      return "do_action";
		}
		return "";
	}
	public function all_action()
	{ 
		$currentAction  = current_filter(); //same as current_action https://developer.wordpress.org/reference/functions/current_action/
		if ( $this->parent_type() == "do_action")
		{
			$GLOBALS['DFTwp_counter'][$currentAction]['idx'] = empty($GLOBALS['DFTwp_counter'][$currentAction]['idx']) ? 1 : $GLOBALS['DFTwp_counter'][$currentAction]['idx']+1;
			add_action($currentAction, [$this,'action_start'],	-1, 99);
			add_action($currentAction, [$this,'action_end'],	9999, 99);
		}
	}

	public function action_start()
	{
		$currentAction  = current_filter();
		$idx= $GLOBALS['DFTwp_counter'][$currentAction]['idx'];
		$this->actions[$currentAction][$idx]['start']=  [
			'time'      => $this->_time(),
		];
		
		remove_action($currentAction, [$this,'action_start'], -1);
		//'arguments' => print_r(func_get_args(),true) // this is overkill, cant be used: 
		$args= func_get_args();
		return $args[0];
	}


	public function action_end()
	{ 
		$currentAction  = current_filter(); 
		$idx= $GLOBALS['DFTwp_counter'][$currentAction]['idx'];

		$this->actions[$currentAction][$idx]['end'] =  [
			'time'      => $this->_time()  
		];
		remove_action($currentAction, [$this,'action_end'], 9999);
	}
	// ########################################################################### //
	
	
	

	public function footer_enable_flag()
	{  
		$this->allow_flag =true;
	}
	private $implementationKey = 'DFTwp_implemented_11';
		
	public function shutdown()
	{ 
		if (!did_action('plugins_loaded')) return;
		if ( !$this->is_editor() )	return;  //late for: if (isset($_COOKIE['DFTwp_enable'])) setcookie('DFTwp_enable', 0, time()-86400);
		if ( !$this->allow_flag )	return;
		define('DFTwp_END_TIME', $this->_time() );

		if ( !$this->enabled_display() )
		{
			?>
			<div style="background:red; padding:10px;">Function timings disabled. Visit admin-dasbhoard once, to activate it for you.</div>
			<?php
			return;
		}

		$this->CheckDefines(); 
		?>
		<style>
		#trace_debug {  width:auto; max-width: 1000px; margin: 5px auto 0; background: #bdbdbd; padding: 5px; border: 1px solid gray; border-radius: 50px 50px 0 0; font-family: arial;  line-height:1.2; z-index: 8888;  font-size: 16pt; position: fixed; bottom: 0px; right:0px; left:0px;} 
		#trace_debug .headRow{ text-align:left; } 
		#trace_debug.active { height:90%; overflow-y: scroll; } 
		#trace_debug.inactive { height: 70px; overflow: hidden; } 
		#trace_debug .title1{ font-size: 2.8125em; font-weight:bold; text-align:center; } 
		#trace_debug .title1 a:focus{ outline:none; box-shadow:none; } 
		#trace_debug .head_tr th{ cursor:pointer; } 
		#trace_debug tr{ vertical-align: baseline; } 
		#trace_debug .notic{ white-space: normal; color:red; } 
		#trace_debug .first_row{font-weight:bold; }
		#trace_debug .footer_tr{font-size:12px;}
		#trace_debug .head_tr, #trace_debug .footer_tr{ background:#e7e7e7; color: #0072ff; }
		#trace_debug table{ width:100%; }
		#trace_debug table td{ padding: 0.5em 0 0.5em 0.5em; }

		#trace_debug .triggered_times{ width: 45px; font-size: 0.5em; padding: 0; font-weight: normal; }
		#trace_debug .complete_length { width: 200px; }
		#trace_debug .dft_parentTitle{font-weight:bold;  } 
		#trace_debug .dft_childWrap{display:flex;  font-size:0.7em; flex-direction:column;  } 
		#trace_debug .dft_child { height:30px; display:flex; flex-direction: column; line-height: 1em; padding: 1px 0px 0px 50px;}
		zz#trace_debug .dft_child:nth-child(odd) { background: #e7e7e7e7; }
		#trace_debug .dft_child.separator{ padding:0 0 0 15px; height:5px; background: #5b697b;  } 
		#trace_debug .func_name {}
		#trace_debug .dft_actionsList { border:1px solid #e7e7e7; border-width: 1px 0 1px 1px; }
		#trace_debug .func_path { margin: 0px 5px; font-size:0.65em; font-weight:bold; color:#905e14; }
		</style>
		<div id="trace_debug"  class="inactive"> 
			<script>
			function show_fully()
			{
				var el=document.getElementById("trace_debug");
				el.className = el.className.indexOf("inactive") < 0 ? el.className + " inactive" : el.className.replace("inactive","active");
				el.scrollIntoView();
				sort_table_all_();
			}
			</script>
			<div class="title1"><a href="javascript:show_fully();">Functions Timing results</a></div>

			
			<?php
			$output = '';
			$time_total =0;
			 
			if ($this->standard_way) 
			{
				foreach($this->actions as $name=>$array) { 
					$output .= '<tr>';
					$output .= '<td>'. count($array) .'</td>';
					$output .= '<td>'. $name .'</td>';
					$action_total = 0;
					foreach($array as $randKey=>$array1) {
						$mult = 10000;
						$action_total = $action_total + ($array1['end']['time'] *$mult  - $array1['start']['time'] *$mult)/$mult;
						// $this->decimal_normalizer($array1['start']['time'], 4, true) .'</td>'; 
					}
					$output .= '<td>'. $this->decimal_outputer( $action_total, 4) .'</td>';
					$output .= '</tr>';
					$time_total += $action_total;
				}
			}
			else
			{
				// my tryout to sort them with PHP, failed...  pastebin(dot)com/raw/qcmw6pbc 
				if (!array_key_exists('DFTwp_ARRAY', $GLOBALS)) echo '<h1 style="color:red;">Reload page to start getting data</h1>';
				else 
				foreach($GLOBALS['DFTwp_ARRAY'] as $actionName=>$array) 
				{ 
					$childDiv =[];
					$childTimes =[];
					$children = [];
					$count_action = count($array);
					$action_total = 0;

					$i=0;
					foreach($array as $actionCycleIndex=>$arrayOfFuncs) { $i++;
						$children[] = $arrayOfFuncs;
						
						foreach($arrayOfFuncs as $funcName=>$arrayOfBlocks)	{
							$count_func =  count($arrayOfBlocks);
							$child_total = 0;
							foreach($arrayOfBlocks as $funcCycleIndex=>$funcBlock)	{
								$mult = 10000; // NEEDED, TO CONVERT TO DECIMAL CORRECTLY
								if (!empty($funcBlock['end']))
								{
									if ( empty($funcBlock['start']) ){
										// STRANGE !!!
									}
									else{
										$child_total += ($funcBlock['end'] *$mult  - $funcBlock['start'] *$mult)/$mult;
									}
								}
							}
							$action_total += $child_total;

							$txt= '<div class="dft_child"> <div class="func_name">'.$funcName .' <span class="amount">('.$count_func.')</span></div> <div class="func_path">'. $GLOBALS['DFTwp_func_paths'][$funcName].'</div></div>';
							$childDiv[]	 = $txt;
							$txt= '<div class="dft_child">'. $this->decimal_outputer( $child_total, 5) .'</div>';
							$childTimes[]= $txt;
						}

						if ( count($array) > 0  &&  count($array) != $i)
						{
							$childDiv[]	 = '<div class="dft_child separator"></div>';
							$childTimes[]= '<div class="dft_child separator"></div>';
						}
					}
					
					$childDiv_final = implode('', $childDiv);
					$childTimes_final = implode('', $childTimes);

					$output .= '<tr class="dft_tr">';
					$output .=	'<td>'. $count_action .'</td>';
					$output .=	'<td class="dft_actionsList"><span class="dft_parentTitle">'. $actionName .'</span><div class="dft_childWrap">'.$childDiv_final.'</div></td>';
					$output .=	'<td class="dft_timessList"><span class="dft_parentTitle">'. $this->decimal_outputer( $action_total, 5) .'</span><div class="dft_childWrap">'.$childTimes_final.'</div></td>';
					$output .= '</tr>';

					$time_total += $action_total;
				}
			} 
			?>


			<div class="headRow">
				<div class="first_row">• Total time of page's PHP execution (from the first moment to last moment): <b><?php echo $this->decimal_outputer( DFTwp_END_TIME - DFTwp_START_TIME, 2); ?></b> seconds </div>
				<br/>
				<div class="second_row">• Sum time of actions & filters execution : <b id="totalsum_actions"><?php echo $this->decimal_outputer( $time_total, 2); ?></b> seconds (<span class="notic"> This value is just an approximation, because one action is typically executed inside another parent action, so, summing up them causes doubled/incorrect calculations. Instead, the wise decision could be to just emphasize on fixing the really slow functions.</span>) </div>  
			</div>
			<table class="dft_table"> <script> document.getElementsByClassName("dft_table")[0].style.visibility = "hidden";</script>
 			<thead>
				<tr class="head_tr">
					<th class="triggered_times">trigger<br/>count</th><th class="func_name">Action & Function name</th><th class="complete_length">complete length (seconds)</th>
				</tr>
  			</thead>
  			<tbody>
			<?php
			  echo $output;
			?>
  			</tbody>
			</table>


		</div>
		
		<script> 
		var MakeSortable = {
			
			ascending : false,

			getCellValue : function (tr, idx) { return tr.children[idx].innerText || tr.children[idx].textContent; },

			comparer : 
				(idx, asc) => 
				(a, b) => 
				((v1, v2) => v1 !== '' && v2 !== '' && !isNaN(v1) && !isNaN(v2) ? v1 - v2 : v1.toString().localeCompare(v2))
				( MakeSortable.getCellValue( this.ascending ? (asc ? a : b) :  (asc ? b : a) , idx), MakeSortable.getCellValue(  this.ascending ? (asc ? b : a) :  (asc ? a : b)  , idx) )
			,

			start: function(table) {
				if (!table) return;
				table.querySelectorAll('th').forEach(th => th.addEventListener('click', (() => {
					const table = th.closest('table');
					const tbody = table.querySelector('tbody');
					Array.from(tbody.querySelectorAll('tr'))
						.sort(MakeSortable.comparer(Array.from(th.parentNode.children).indexOf(th), this.asc = !this.asc))
						.forEach(tr => tbody.appendChild(tr) );
				})));
			}

		};

		var mainTABLE_ = document.getElementsByClassName("dft_table")[0];
		function sort_table_all_()
		{
			MakeSortable.start( mainTABLE_ );
			mainTABLE_.getElementsByClassName("complete_length")[0].click();
		}
		
		window.onload= function()
		{
			mainTABLE_.style.visibility="visible";
		};
		</script>
		<?php
	}




	private $hookFile 		= ABSPATH . WPINC .'/class-wp-hook.php';
	private function hookFileBackup(){ return ABSPATH . WPINC .'/class-wp-hook.php__DFTwp_BACKUP__'.time().'.bak'; }
	
	public function CheckDefines()
	{
		$redirect=0;
		$error=0;

		if (!isset($GLOBALS["DFTwp_ENABLE_TIMER"]))
		{
			// Insert the code in wp-config.php
			$file = ABSPATH.'/wp-config.php';
			$code = '$dbg_include="'. str_replace( ['\\','/'], '/', __DIR__.'/wp-loader-addition.php') .'"; if(file_exists($dbg_include)) { include_once($dbg_include); }';
			$content = file_get_contents($file);
			if( strpos($content, $code)===false )
			{
				if ( file_put_contents( $file,  preg_replace('/\/\* That\'s all\, stop editing/', $code. PHP_EOL . PHP_EOL.'$0', $content ) ) )
				{
					$redirect=1;
				}
				else
				{
					$error=1;
				}
			}
		}

		if (!isset($GLOBALS[$this->implementationKey]))	///if called too early, wont be obtained maybe..
		{
			// Insert the callback in hook...  Ahh, Err, what's that? ... that is ...  Well, yes, that is my approach to implement what we want..
			$content = file_get_contents($this->hookFile);
			if( stripos($content, $this->implementationKey)===false )
			{
				// if backup already existed previous version plugin
				if (file_exists($this->hookFileBackup()))
				{
					unlink($this->hookFile);
					$content = file_get_contents($this->hookFileBackup());
				}
				else
				{
					rename($this->hookFile, $this->hookFileBackup());
				}

				$code = PHP_EOL.'$_debug= array_key_exists("DFTwp_ENABLE_TIMER", $GLOBALS) && $GLOBALS["DFTwp_ENABLE_TIMER"]; $GLOBALS["'.$this->implementationKey.'"]=1;'. PHP_EOL;
				$content =preg_replace('/function apply_filters\( \$value\, \$args \) \{/',  '$0'.$code, $content );

				$code = PHP_EOL.'if($_debug) DFTwp_CALLBACK_ACTION_START( $value, $args, $this );'. PHP_EOL;
				$content =preg_replace('/\$nesting_level \= \$this\-\>nesting_level\+\+/',  $code.'$0', $content );

				$code = PHP_EOL.'if($_debug) DFTwp_CALLBACK_FUNCTION_START( $the_, func_get_args(), $this );'. PHP_EOL;
				$content =preg_replace('/\/\/ Avoid the array_slice/',  $code.'$0', $content );

				$code = PHP_EOL.'if($_debug) DFTwp_CALLBACK_FUNCTION_END($the_, func_get_args(), $this);'. PHP_EOL;
				$content =preg_replace('/\}\n\t\t\t}\n\t\t} while/',  '}'.$code. "\n\t\t\t}\n\t\t} while", $content );
				
				$code = PHP_EOL.'if($_debug) DFTwp_CALLBACK_ACTION_END( $value, $args, $this );'. PHP_EOL;
				$content =preg_replace('/unset\( \\$this\-\>current_priority\[ \$nesting_level \] \)\;/',  '$0'.$code, $content );
	
				if ( file_put_contents($this->hookFile, $content) )
				{
					$redirect=2;  
				}
				else
				{
					$error=2;
				}
			}
		}
		if ($error) { _e("cant write file. DebugFunctionsTime cant work here. Check File-write permissions"); }
		else if ($redirect) {$this->reload(0.5); }
	}



	
	
	
	
	
	
	
	
	
	
	
	//============== just helper funcs =========== 
	public function _time() { return floatval(microtime(true)); }
	public function reload($sec=0) { exit('<script>window.setTimeout( function(){ window.location.href = window.location.href;}, '.($sec*1000).');</script>'); }
	public function is_editor()	{ if (function_exists('current_user_can') || require_once(ABSPATH.'wp-includes/pluggable.php')) return (current_user_can('edit_others_posts')); return false; }
	
	public function decimal_outputer($input, $length=4, $only_dot=false){  
		if($only_dot)
		{
			//this is not good for full numbers..
			$timeParts = explode('.', $input);
			return substr($timeParts[1], 0, $length);
		}
		else {
			return sprintf('%.'.$length.'F',  $input);
		}
	}

	public function decimal_normalizer($scientific_notation)
	{
		$float = sprintf('%f', $scientific_notation);
		$integer = sprintf('%d', $scientific_notation);
		// if this is a whole number, remove all decimals, else remove trailing zeroes from the decimal portion
		$output = $float == $integer ? $integer : rtrim(  rtrim($float,'0') ,  '.');
		return $output;
	} 
	public function array_sort_by_column(&$arr, $col, $dir = SORT_ASC) {
		$sort_col = [];
		foreach ($arr as $key=> $row) {
			$sort_col[$key] = $row[$col];
		}
		array_multisort($sort_col, $dir, $arr);
		return $arr;
	}



	public function enable_display_set()
	{
		if ($this->is_editor()) 
		{ 
			if( ! $this->enabled_display() )
			{ 
				update_site_option('DFTwp_last_ip', $this->ip);
				setcookie ( 'DFTwp_enable' , 1 , time()+86400*30, $path = '/', $_SERVER['HTTP_HOST'],  null,  $httponly=true  );
				header("location:". $_SERVER['REQUEST_URI'], true, 302); exit;
			}
		}
	}

	private function enabled_display()
	{
		return isset($_COOKIE['DFTwp_enable']) && get_site_option('DFTwp_last_ip') ==  $this->ip ;
	}


  }

  $GLOBALS[__NAMESPACE__] = new TestTimes();

}
 






/* TODO  :    integration into  "Debug Bar" plugin

namespace DebugProfilerFunctionsTime
{

	class Debug_Profiler_Functions_Time extends Debug_Bar_Panel 
	{
		function init() {
			$this->title( 'Console' ); 
		}

		function prerender() {
			$this->set_visible( true );
		}

		function render() {
			?>
			<div class="mainDiv">
			</div>
			<?php
		}

	}

	add_filter('debug_bar_panels',  function($panels){ $panels[] = new Debug_Profiler_Functions_Time(); return $panels; } );
}


*/








































#region ======= THIS IS OUR COMPANY's STANDARD NAMESPACE BLOCK. IT IS SAFE TO REMOVE THIS CODE IN YOUR FORKS, PLUGIN WILL STILL WORK. ========== //
namespace DebugFunctionsTime
{
  if (!defined('ABSPATH')) exit;
  require_once( __DIR__."/library.php" );
  require_once( __DIR__."/library_wp.php" );
  
  class PluginClass extends \Puvox\wp_plugin
  {

	public function declare_settings()
	{
		$this->initial_static_options	= 
		[
			'has_pro_version'        => 0, 
            'show_opts'              => true, 
            'show_rating_message'    => true, 
            'show_donation_footer'   => true, 
            'show_donation_popup'    => true, 
            'menu_pages'             => [
                'first' =>[
                    'title'           => 'Find Slow Functions', 
                    'default_managed' => 'network',            // network | singlesite
                    'required_role'   => 'install_plugins',
                    'level'           => 'submenu', 
                    'page_title'      => 'Find Slow Functions',
                    'tabs'            => [],
                ],
            ]
		];

		$this->initial_user_options		= 
		[
			'sample'	=> false
		];
	}

	public function __construct_my()
	{
		//$this->register_stylescript('wp', 'style', 'my_styles', 'assets/styles.css');
	}

	// ============================================================================================================== //
	// ============================================================================================================== //


	// =================================== Options page ================================ //
	public function opts_page_output()
	{ 
		$this->settings_page_part("start", 'first');
		?> 

		<style>
		.myplugin {padding:10px;}
		</style>
		
		<?php if ($this->active_tab=="Options") 
		{
		?> 

			<p><?php _e('This plugin works simply - You need to be logged-in with (at least) "editor" role, and then visit any page of your site. <br/><br/>Note: If you will ever use "Debug Bag - Slow Actions" plugin, we strongly recomment to deactivate it after you finish testing, because per our testing, it doubles the page-execution time ( even to regular visitors )', '');?></p>
			
		<?php 
		} 
		
		$this->settings_page_part("end", '');
	} 
	public function deactivation_funcs($network_wide)
	{ 
		setcookie('DFTwp_enable', 0, time()-86400*7);
	}

  } // End Of Class

  $GLOBALS[__NAMESPACE__] = new PluginClass();

} // End Of NameSpace
#endregion

 


