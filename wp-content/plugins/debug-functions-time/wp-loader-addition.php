<?php
// this is included from wp_load, so check only if called from there
namespace 
{
	if ( isset($_COOKIE['DFTwp_enable']) )
	{ 
		$GLOBALS["DFTwp_ENABLE_TIMER"] = true;

		define("DFTwp_START_TIME", floatval(microtime(true)) ); 
 
		function DFTwp_CALLBACK_ACTION_START($value, $args, $class)
		{
			if(!$GLOBALS["DFTwp_ENABLE_TIMER"]) return;

			$currentAction=current_filter();
			$GLOBALS['DFTwp_idx_action'][$currentAction]['idx'] = $idx_action = (empty($GLOBALS['DFTwp_idx_action'][$currentAction]['idx']) ?  0 : $GLOBALS['DFTwp_idx_action'][$currentAction]['idx']) + 1; //( DFTwp_GET_VALUE_($class, 'doing_action') ? 1 : 0)
		}

		function DFTwp_CALLBACK_FUNCTION_START($the_, $args, $class)
		{
			if(!$GLOBALS["DFTwp_ENABLE_TIMER"]) return;

			$currentAction=current_filter();
			DFTwp_function_name_detect($the_, $func_name, $filePath); 
			$idx_action = $GLOBALS['DFTwp_idx_action'][$currentAction]['idx'];
			$GLOBALS['DFTwp_func_paths'][$func_name]= $filePath;

			$GLOBALS['DFTwp_idx_func'][$currentAction][$func_name]['idx'] = $idx_func = (empty($GLOBALS['DFTwp_idx_func'][$currentAction][$func_name]['idx']) ?  0 : $GLOBALS['DFTwp_idx_func'][$currentAction][$func_name]['idx']) +1;
			$GLOBALS['DFTwp_ARRAY'][$currentAction][ $idx_action ][$func_name][$idx_func]["start"]	= microtime(true);
			
		}

		function DFTwp_CALLBACK_FUNCTION_END($the_, $args, $class)
		{
			if(!$GLOBALS["DFTwp_ENABLE_TIMER"]) return;

			$currentAction=current_filter();
			DFTwp_function_name_detect($the_, $func_name, $filePath); 

			$idx_action	= empty($GLOBALS['DFTwp_idx_action'][$currentAction]['idx']) ?  1 : $GLOBALS['DFTwp_idx_action'][$currentAction]['idx'];
			$idx_func	= $GLOBALS['DFTwp_idx_func'][$currentAction][$func_name]['idx'];
			$GLOBALS['DFTwp_ARRAY'][$currentAction][ $idx_action ][$func_name][$idx_func]["end"]	= microtime(true);
		}


		function DFTwp_CALLBACK_ACTION_END($value, $args, $class)
		{
			$currentAction=current_filter();
			if(!$GLOBALS["DFTwp_ENABLE_TIMER"]) return;
			
		}


		function DFTwp_function_name_detect($callback, &$funcName, &$filePath) {
			$cb_name ='';
			$error=0;
			$FUNC = $callback['function'];
			try{
				// https://www.php.net/manual/en/language.types.callable.php
				if (is_callable($FUNC) )
				{
					// function passed as string  | i.e.  'myFunction'
					if ( is_string($FUNC) )
					{
						if ( function_exists($FUNC) ) {
							$rf = new ReflectionFunction($FUNC);
							$filePath = $rf->getFileName() . '::'.$rf->getStartLine();
							$funcName = sprintf( '%s', $rf->getShortName () );
						} 
						elseif (stripos($FUNC,'::') !== false) // function passed as static method | i.e. 'namespaceName::func'
						{
							$className= preg_replace('/\:\:(.*)/', '', $FUNC);
							$funcName = preg_replace('/(.*)\:\:/', '', $FUNC);
							$rc = new ReflectionClass($className);
							$rm = $rc->getMethod($funcName);
							$filePath = $rm->getFileName() . '::'.$rm->getStartLine();
							$funcName = sprintf( '%s', $rm->getShortName() );
						}
						else { $error = 1; } 
					}
					// function passed as closure | i.e.  function(){}  [ same as gettype -object]
					elseif ( $FUNC instanceof Closure )   { 
						// Probably an anonymous (closure) function.
						$rf = new ReflectionFunction($FUNC);
						$obj_or_class = $rf->getClosureThis();
						$className = (empty($obj_or_class) ? "" : (is_object($obj_or_class) ? get_class($obj_or_class) : print_r($obj_or_class,true) ) );
						$filePath = $rf->getFileName() . '::'.$rf->getStartLine(); 
						$funcName = sprintf( '%s::%s', $className, $rf->getShortName() );
					} 
					// function passed as invokable class | i.e. ExampleClass
					elseif ( is_object($FUNC) ) {  //gettype($FUNC)==='object')
						$rf           = new ReflectionClass ($FUNC);
						$className    = $rf->getName();
						$filePath     = $rf->getFileName() . '::'.$rf->getStartLine(); 
						$funcName     = $className;
					}  
					// function passed as array 
					elseif ( is_array( $FUNC ) ) {
						// i.e.  [$class, 'methodName']
						if ( count( $FUNC ) == 2 )
						{
							list( $object_or_class, $method ) = $FUNC;
							//if ( is_object( $object_or_class ) )  $className = get_class( $object_or_class ); 
							$rc = new ReflectionClass($object_or_class);
							$className = $rc->getName();
							$filePath = $rc->getFileName(); //.'::'.$rc->getStartLine ();
							$funcName = sprintf( '%s::%s', $className, $method );
						}
						else { $error = 2; } 
					}
					else { $error = 3; }
					//
					if ( empty($error) )
					{
						$filePath = strpos($filePath, 'wp-content') !== false ?  
						preg_replace('/(.*?)(\/|\\\\)wp\-content(\/|\\\\)/', '', $filePath)  :     
						preg_replace('/(.*?)(\/|\\\\)(wp\-includes|wp\-admin)(\/|\\\\)/', '$3/', $filePath);
					} //(is_string($f) && function_exists($f)) || (is_object($f) && ($f instanceof Closure));
				}
				else { $error = 4; }
			}
			catch(\Exception $ex){
				$error = 5;
			}
			if (!empty($error))
			{
				$rf       = "cant detect";
				$filePath = "cant detect";
				$method   = "cant detect";
				$funcName = "cant detect. err_$error. ". print_r($callback, true);
			}
		}


		function DFTwp_GET_VALUE_($obj, $propName)
		{
			$r = new ReflectionObject($obj);
			$p = $r->getProperty($propName);
			$p->setAccessible(true);
			return $p->getValue($obj);
		}



	}
}
