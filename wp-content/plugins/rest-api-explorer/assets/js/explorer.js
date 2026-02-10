/*!
This file is part of REST API Explorer. For copyright and licensing information, please see ../../license/license.txt
*/

jQuery(document).ready(function($) {
	function buildTreeNavigator(currentNode, currentPath, $currentElement) {
		for (var child in currentNode) {
			var $newElement = $('<li>');
			var childPath = currentPath ? currentPath + (currentPath === '/' ? '' : '/') + child : child;
			$newElement.append( $('<a>').attr('href', '#').attr('data-path', childPath).text(child) );
			if (Object.keys( currentNode[child] ).length) {
				$newElement.prepend( $('<a>').attr('href', '#').addClass('rest-api-explorer-toggle') );
				buildTreeNavigator(currentNode[child], childPath, $('<ul>').appendTo($newElement));
			}
			$currentElement.append( $newElement );
		}
	}
	
	function showRoutes(path) {
		var routes = [];
		for (var i = 0; i < phplugins_rest_api_data.length; ++i) {
			if ('/' + phplugins_rest_api_data[i].path.join('/') === path) {
				routes.push(phplugins_rest_api_data[i]);
			}
		}
		
		var $routes = $('#rest-api-explorer > div').empty();
		
		if (routes.length) {
			
			for (var i = 0; i < routes.length; ++i) {
				var $route = $('<div>').attr('data-endpoint', routes[i].path.join('/'));
				
				$route.append(
					$('<h2>').text('/' + routes[i].path.join('/')).prepend(
						$('<span>').addClass('rest-api-explorer-method rest-api-explorer-method-' + routes[i].method.toLowerCase()).text(routes[i].method.toUpperCase())
					).append( $('<button>').attr('type', 'button').addClass('button-primary rest-api-explorer-run').text('Run') )
				);
				
				if (routes[i].params && routes[i].params.length) {
					var $params = $('<dl>');
					for (var j = 0; j < routes[i].params.length; ++j) {
						var $paramTitle = $('<dt>').text(routes[i].params[j].name).appendTo($params);
						var $paramContent = $('<dd>').appendTo($params);
						$('<p>').text(routes[i].params[j].description ? routes[i].params[j].description : 'No description provided').appendTo($paramContent);
						if (routes[i].params[j].type) {
							$('<p>').addClass('rest-api-explorer-param-type').text(routes[i].params[j].type).prepend( $('<strong>').text('Type: ') ).appendTo($paramContent);
						}
						if (routes[i].params[j].choices && routes[i].params[j].choices.length) {
							$('<p>').addClass('rest-api-explorer-param-choices').text(routes[i].params[j].choices.join(', ')).prepend( $('<strong>').text('Allowed values: ') ).appendTo($paramContent);
						}
						if (routes[i].params[j].default) {
							$('<p>').addClass('rest-api-explorer-param-default').text(routes[i].params[j].default).prepend( $('<strong>').text('Default: ') ).appendTo($paramContent);
						}
						var allowsMultipleValues = routes[i].params[j].type && routes[i].params[j].type.substring(routes[i].params[j].type.length - 2) == '[]';
						$('<p>').addClass('rest-api-explorer-param-value')
							.prepend( $('<strong>').text(allowsMultipleValues ? 'Value(s): ' : 'Value: ') )
							.append(
								routes[i].params[j].choices && routes[i].params[j].choices.length
									? $('<select>').attr({
										type: 'text',
										name: routes[i].params[j].name
									})
									.append(
										routes[i].params[j].choices.map( function(choice) {
											return $('<option>').val(choice).text(choice);
										} )
									)
									.val(routes[i].params[j].default ? routes[i].params[j].default : routes[i].params[j].choices[0])
									: $('<input>').attr({
										type: 'text',
										name: routes[i].params[j].name
									})
								.val(routes[i].params[j].default ? routes[i].params[j].default : '')
							)
							.append(
								allowsMultipleValues
									? $('<span>').addClass('rest-api-explorer-param-value-multiple-note').text('(comma-separated)')
									: []
							)
							.appendTo($paramContent);
					}
					$params.appendTo($route);
				}
				
				$route.appendTo($routes);
			}
			
		} else {
			
			$('<p>').text('There are no routes at this path level; please select a child path.').appendTo($routes);
			
		}
	}
	
	if (window.phplugins_rest_api_data) {
		var tree = {'/': {}};
		for (var i = 0; i < phplugins_rest_api_data.length; ++i) {
			var path = phplugins_rest_api_data[i].path;
			if (path.length && (path.length > 1 || path[0].length)) {
				var currentTreePosition = tree['/'];
				for (var j = 0; j < path.length; ++j) {
					if (!currentTreePosition[ path[j] ]) {
						currentTreePosition[ path[j] ] = {};
					}
					currentTreePosition = currentTreePosition[ path[j] ];
				}
			}
		}
		
		buildTreeNavigator(tree, '', $('#rest-api-explorer > ul'));
		
		$('#rest-api-explorer > ul > li').addClass('rest-api-explorer-expanded');
	}
	
	function runEndpoint($endpoint) {
		var endpoint = $endpoint.attr('data-endpoint').split('/');
		console.log(endpoint);
		var endpointData, match;
		for (var i = 0; i < window.phplugins_rest_api_data.length; ++i) {
			endpointData = window.phplugins_rest_api_data[i];
			match = false;
			if (endpointData.path.length == endpoint.length) {
				match = true;
				for (var j = 0; j < endpoint.length; ++j) {
					if (endpoint[j] != endpointData.path[j]) {
						match = false;
						break;
					}
				}
				if (match) {
					break;
				}
			}
		}
		if (!match) {
			return;
		}
		
		var $form = $('<form>').attr({
			action: phplugins_rest_api_config.root_url + endpoint.join('/'),
			method: endpointData.method,
			target: '_blank'
		}).append(
			$('<input>').attr({
				type: 'hidden',
				name: '_wpnonce'
			}).val(phplugins_rest_api_config.nonce)
		);
		
		for (var i = 0; i < endpointData.params.length; ++i) {
			var $field = $endpoint.find(':input[name="' + endpointData.params[i].name + '"]');
			if ($field.length && $field.val()) {
				if (endpointData.params[i].type && endpointData.params[i].type.substring(endpointData.params[i].type.length - 2) == '[]') {
					var key = endpointData.params[i].name + '[]';
					var values = $field.val().split(',');
				} else {
					var key = endpointData.params[i].name;
					var values = [ $field.val() ];
				}
				
				if ($form.attr('action').indexOf('<' + key + '>') == -1) {
				
					$form.append(
						values.map( function(value) {
							return $('<input>').attr({
								type: 'hidden',
								name: key
							}).val(value);
						} )
					);
				
				} else {
					$form.attr('action', $form.attr('action').replace( '<' + key + '>', values[0] ));
				}
			}
		}
		
		$form.appendTo(document.body).submit().remove();
		
	}
	
	$('#rest-api-explorer').on('click', '.rest-api-explorer-toggle', function() {
		$(this).parent().toggleClass('rest-api-explorer-expanded');
		return false;
	});
	$('#rest-api-explorer').on('click', 'a[data-path]', function() {
		showRoutes($(this).attr('data-path'));
		return false;
	});
	
	$('#rest-api-explorer').on('click', '.rest-api-explorer-run', function() {
		runEndpoint($(this).parent().parent());
		return false;
	});
});