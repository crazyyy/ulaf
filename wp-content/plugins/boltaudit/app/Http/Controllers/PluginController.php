<?php

namespace BoltAudit\App\Http\Controllers;

use BoltAudit\App\Repositories\SinglePluginRepository;
use BoltAudit\WpMVC\RequestValidator\Validator;
use BoltAudit\WpMVC\Routing\Response;
use Exception;
use WP_REST_Request;

class PluginController extends Controller {

	public function index( Validator $validator, WP_REST_Request $wp_rest_request ) {
		$validator->validate(
			[
				'slug' => 'string|required',
			]
		);

		$slug = trim( (string) $wp_rest_request->get_param( 'slug' ) );

		try {

			$response = SinglePluginRepository::get_plugin_page_data( $slug );

			return Response::send( $response );

		} catch ( Exception $e ) {
			return Response::send(
				[
					'message' => $e->getMessage(),
				],
				500
			);
		}
	}
}