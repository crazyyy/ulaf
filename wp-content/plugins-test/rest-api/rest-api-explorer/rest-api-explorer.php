<?php
/*
 * Plugin Name:       REST API Explorer
 * Version:           1.0.3
 * Description:       Explore the WordPress REST API right in the WordPress admin!
 * Author:            BerryPress
 * Plugin URI:        https://berrypress.com/
 * License:           GPLv3+
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       rest-api-explorer
 * GitHub Plugin URI: BerryPress/rest-api-explorer
 */

namespace Penthouse\RestApiExplorer;

class RestApiExplorer {
	const VERSION = '1.0.3';
	
	private static $instance;

	public static function instance() {
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		add_action('admin_menu', [$this, 'onAdminMenu']);
		add_action('admin_enqueue_scripts', [$this, 'adminScripts']);
	}
	
	public function onAdminMenu() {
		add_management_page(__('REST API Explorer', 'rest-api-explorer'), __('REST API Explorer', 'rest-api-explorer'), 'manage_options', 'rest-api-explorer', [$this, 'adminPage']);
	}
	
	public function adminPage() {
		echo('
			<h1>REST API Explorer</h1>
			<div class="wrap">
				<div id="rest-api-explorer">
					<ul></ul>
					<div></div>
				</div>
			</div>
		');
	}
	
	public function adminScripts($screenId) {
		if ($screenId == 'tools_page_rest-api-explorer' && current_user_can('manage_options')) {
			wp_enqueue_script('rest-api-explorer', plugin_dir_url(__FILE__).'assets/js/explorer.js', ['jquery'], self::VERSION, true);
			wp_localize_script('rest-api-explorer', 'phplugins_rest_api_config', [
				'root_url' => rest_url(),
				'nonce' => wp_create_nonce('wp_rest')
			] );
			wp_localize_script('rest-api-explorer', 'phplugins_rest_api_data', $this->getData());
			wp_enqueue_style('rest-api-explorer', plugin_dir_url(__FILE__).'assets/css/explorer.css', [], self::VERSION);
		}
	}
	
	public function getData() {
		$server = rest_get_server();
		$routes = $server->get_routes();
		$data = [];
		
		foreach ($routes as $routeId => $routes) {
			foreach ($routes as $route) {
				$routePath = explode('/', substr($routeId, 1));
				
				foreach ($routePath as &$routePathPart) {
					if (substr($routePathPart, 0, 4) == '(?P<' && strpos($routePathPart, '>', 4)) {
						$routePathPart = substr($routePathPart, 3, strpos($routePathPart, '>', 4) - 2);
					}
				}
				
				$routeArgs = empty($route['args']) ? [] : $this->getArgs($route['args']);
				
				foreach ($route['methods'] as $method => $isEnabled) {
					if ($isEnabled) {
						
						$data[] = [
							'path' => $routePath,
							'method' => $method,
							'params' => $routeArgs
						];
						
					}
				}
				
				
			}
		}
		
		return $data;
	}
	
	private function getArgs($args) {
		$routeArgs = [];
		foreach ($args as $argId => $arg) {
			$routeArg = [
				'name' => $argId
			];
			if (!empty($arg['description'])) {
				$routeArg['description'] = $arg['description'];
			}
			if (!empty($arg['type'])) {
				if ($arg['type'] == 'array' && isset($arg['items']['type'])) {
					if (is_array($arg['items']['type'])) {
						$routeArg['type'] = '';
						foreach ($arg['items']['type'] as $type) {
							$routeArg['type'] .= ($routeArg['type'] ? '|' : '').$type.'[]';
						}
					} else {
						$routeArg['type'] = $arg['items']['type'].'[]';
					}
					if (isset($arg['items']['properties'])) {
						$routeArg['subfields'] = $this->getArgs($arg['items']['properties']);
					}
				} else {
					$routeArg['type'] = is_array($arg['type']) ? implode('|', $arg['type']) : $arg['type'];
				}
			}
			if (isset($arg['default'])) {
				$routeArg['default'] = $arg['default'];
			}
			if (isset($arg['enum'])) {
				$routeArg['choices'] = $arg['enum'];
			}
			if (!empty($arg['readonly'])) {
				$routeArg['readonly'] = true;
			}
			if (isset($arg['properties']) && !isset($routeArg['subfields'])) {
				$routeArg['subfields'] = $this->getArgs($arg['properties']);
			}
			
			
			$routeArgs[] = $routeArg;
		}
		
		return $routeArgs;
	}
}

RestApiExplorer::instance();