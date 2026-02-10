<?php

namespace SolidWP\Mail\Admin\REST;

use SolidWP\Mail\Repository\ProvidersRepository;
use SolidWP\Mail\Service\ConnectionService;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * REST Controller for managing email connections.
 *
 * Provides CRUD operations for email connections including creation,
 * retrieval, updating, deletion, and activation of connections.
 */
class Connections extends \WP_REST_Controller {

	protected $namespace = 'solid-mail/v1';

	protected $rest_base = 'connections';

	protected ProvidersRepository $repository;

	protected ConnectionService $connection_service;

	public function __construct( ProvidersRepository $repository, ConnectionService $connection_service ) {
		$this->repository         = $repository;
		$this->connection_service = $connection_service;
	}

	/**
	 * Registers the REST routes for connection management.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_items' ],
					'permission_callback' => [ $this, 'get_items_permissions_check' ],
				],
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create_item' ],
					'permission_callback' => [ $this, 'create_item_permissions_check' ],
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::CREATABLE ),
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[a-zA-Z0-9_-]+)',
			[
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_item' ],
					'permission_callback' => [ $this, 'get_item_permissions_check' ],
					'args'                => [
						'id' => [
							'description' => __( 'Unique identifier for the connection.', 'LION' ),
							'type'        => 'string',
						],
					],
				],
				[
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_item' ],
					'permission_callback' => [ $this, 'update_item_permissions_check' ],
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::EDITABLE ),
				],
				[
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_item' ],
					'permission_callback' => [ $this, 'delete_item_permissions_check' ],
					'args'                => [
						'id' => [
							'description' => __( 'Unique identifier for the connection.', 'LION' ),
							'type'        => 'string',
						],
					],
				],
			]
		);
	}

	/**
	 * Retrieves all connections.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_REST_Response Response object containing all connections.
	 */
	public function get_items( $request ) {
		$connections = $this->repository->get_all_providers_as_array();
		
		$data = [];
		foreach ( $connections as $connection ) {
			$response = $this->prepare_item_for_response( $connection, $request );
			$data[]   = $this->prepare_response_for_collection( $response );
		}

		return rest_ensure_response( $data );
	}

	/**
	 * Retrieves a specific connection by ID.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_REST_Response|WP_Error Response object or error if connection not found.
	 */
	public function get_item( $request ) {
		$id         = $request['id'];
		$connection = $this->repository->get_provider_by_id( $id );

		if ( ! $connection ) {
			return new WP_Error( 'connection_not_found', __( 'Connection not found', 'LION' ), [ 'status' => 404 ] );
		}

		$data = $this->prepare_item_for_response( $connection->to_array(), $request );
		return rest_ensure_response( $data );
	}

	/**
	 * Creates a new connection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_REST_Response|WP_Error Response object with created connection or error.
	 */
	public function create_item( $request ) {
		$params = $request->get_params();
		
		$result = $this->connection_service->save_connection( $params );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// The service now returns the connection data directly
		$response = $this->prepare_item_for_response( $result, $request );
		$response = rest_ensure_response( $response );
		$response->set_status( 201 );

		return $response;
	}

	/**
	 * Updates an existing connection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_REST_Response|WP_Error Response object with updated connection or error.
	 */
	public function update_item( $request ) {
		$id         = $request['id'];
		$connection = $this->repository->get_provider_by_id( $id );

		if ( ! $connection ) {
			return new WP_Error( 'connection_not_found', __( 'Connection not found', 'LION' ), [ 'status' => 404 ] );
		}

		$params       = $request->get_params();
		$params['id'] = $id; // Ensure ID is included for the service

		$result = $this->connection_service->save_connection( $params );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// The service now returns the connection data directly
		$response = $this->prepare_item_for_response( $result, $request );
		
		return rest_ensure_response( $response );
	}

	/**
	 * Deletes a connection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_REST_Response|WP_Error Response object with deletion status or error.
	 */
	public function delete_item( $request ) {
		$id         = $request['id'];
		$connection = $this->repository->get_provider_by_id( $id );

		if ( ! $connection ) {
			return new WP_Error( 'connection_not_found', __( 'Connection not found', 'LION' ), [ 'status' => 404 ] );
		}

		if ( $connection->is_active() ) {
			return new WP_Error( 'cannot_delete_active', __( 'Cannot delete active connection', 'LION' ), [ 'status' => 400 ] );
		}

		$this->connection_service->delete_connection( $id );

		return new WP_REST_Response( null, \WP_Http::NO_CONTENT );
	}

	/**
	 * Checks permissions for retrieving connections.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return bool|WP_Error True if user can access, WP_Error otherwise.
	 */
	public function get_items_permissions_check( $request ) {
		return $this->check_permissions();
	}

	/**
	 * Checks permissions for retrieving a specific connection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return bool|WP_Error True if user can access, WP_Error otherwise.
	 */
	public function get_item_permissions_check( $request ) {
		return $this->check_permissions();
	}

	/**
	 * Checks permissions for creating a connection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return bool|WP_Error True if user can create, WP_Error otherwise.
	 */
	public function create_item_permissions_check( $request ) {
		return $this->check_permissions();
	}

	/**
	 * Checks permissions for updating a connection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return bool|WP_Error True if user can update, WP_Error otherwise.
	 */
	public function update_item_permissions_check( $request ) {
		return $this->check_permissions();
	}

	/**
	 * Checks permissions for deleting a connection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return bool|WP_Error True if user can delete, WP_Error otherwise.
	 */
	public function delete_item_permissions_check( $request ) {
		return $this->check_permissions();
	}

	/**
	 * Checks if the current user has permission to manage connections.
	 *
	 * @return bool|WP_Error True if user has manage_options capability, WP_Error otherwise.
	 */
	private function check_permissions() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 
				'rest_cannot_manage', 
				__( 'You do not have permissions to manage connections', 'LION' ), 
				[ 'status' => rest_authorization_required_code() ] 
			);
		}

		return true;
	}

	/**
	 * Prepares a connection item for the REST response.
	 *
	 * @param array           $item    Connection data array.
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Prepared response object.
	 */
	public function prepare_item_for_response( $item, $request ) {
		$data = [
			'id'              => $item['id'],
			'name'            => $item['name'],
			'description'     => $item['description'] ?? '',
			'from_email'      => $item['from_email'] ?? '',
			'from_name'       => $item['from_name'] ?? '',
			'is_active'       => $item['is_active'] ?? false,
			'is_default'      => $item['is_default'] ?? false,
			'disable_logs'    => $item['disable_logs'] ?? false,
			'delivery_method' => $item['delivery_method'] ?? 'smtp',
		];

		if ( isset( $item['smtp_host'] ) ) {
			$data['smtp_host'] = $item['smtp_host'];
		}
		if ( isset( $item['smtp_port'] ) ) {
			$data['smtp_port'] = $item['smtp_port'];
		}
		if ( isset( $item['smtp_auth'] ) ) {
			$data['smtp_auth'] = $item['smtp_auth'];
		}
		if ( isset( $item['smtp_username'] ) ) {
			$data['smtp_username'] = $item['smtp_username'];
		}
		if ( isset( $item['smtp_secure'] ) ) {
			$data['smtp_secure'] = $item['smtp_secure'];
		}
		if ( isset( $item['smtp_password'] ) ) {
			$data['smtp_password'] = $item['smtp_password'];
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		$response = rest_ensure_response( $data );

		return apply_filters( 'rest_prepare_connection', $response, $item, $request );
	}

	/**
	 * Retrieves the connection schema for REST API documentation.
	 *
	 * @return array Schema array defining connection properties.
	 */
	public function get_item_schema() {
		$schema = [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'connection',
			'type'       => 'object',
			'properties' => [
				'id'              => [
					'description' => __( 'Unique identifier for the connection.', 'LION' ),
					'type'        => 'string',
					'context'     => [ 'view', 'edit' ],
					'readonly'    => true,
				],
				'name'            => [
					'description' => __( 'The connection type name.', 'LION' ),
					'type'        => 'string',
					'context'     => [ 'view', 'edit' ],
					'required'    => true,
					'enum'        => [ 'mailgun', 'brevo', 'sendgrid', 'amazon_ses', 'postmark', 'other' ],
				],
				'description'     => [
					'description' => __( 'Description of the connection.', 'LION' ),
					'type'        => 'string',
					'context'     => [ 'view', 'edit' ],
				],
				'from_email'      => [
					'description' => __( 'The email address used as the sender.', 'LION' ),
					'type'        => 'string',
					'format'      => 'email',
					'context'     => [ 'view', 'edit' ],
				],
				'from_name'       => [
					'description' => __( 'The name used as the sender.', 'LION' ),
					'type'        => 'string',
					'context'     => [ 'view', 'edit' ],
				],
				'is_active'       => [
					'description' => __( 'Whether this connection is active.', 'LION' ),
					'type'        => 'boolean',
					'context'     => [ 'view', 'edit' ],
				],
				'is_default'      => [
					'description' => __( 'Whether this connection is used for email addresses not related to a specific connection.', 'LION' ),
					'type'        => 'boolean',
					'context'     => [ 'view', 'edit' ],
				],
				'disable_logs'    => [
					'description' => __( 'Whether logging is disabled for this connection.', 'LION' ),
					'type'        => 'boolean',
					'context'     => [ 'view', 'edit' ],
				],
				'delivery_method' => [
					'description' => __( 'The delivery method used by the connection.', 'LION' ),
					'type'        => 'string',
					'context'     => [ 'view', 'edit' ],
					'enum'        => [ 'smtp', 'api' ],
				],
				'smtp_host'       => [
					'description' => __( 'SMTP server hostname.', 'LION' ),
					'type'        => 'string',
					'context'     => [ 'view', 'edit' ],
				],
				'smtp_port'       => [
					'description' => __( 'SMTP server port.', 'LION' ),
					'type'        => 'integer',
					'minimum'     => 0,
					'context'     => [ 'view', 'edit' ],
				],
				'smtp_auth'       => [
					'description' => __( 'Whether SMTP authentication is required.', 'LION' ),
					'type'        => 'string',
					'context'     => [ 'view', 'edit' ],
					'enum'        => [ 'yes', 'no' ],
				],
				'smtp_username'   => [
					'description' => __( 'SMTP username for authentication.', 'LION' ),
					'type'        => 'string',
					'context'     => [ 'view', 'edit' ],
				],
				'smtp_password'   => [
					'description' => __( 'SMTP password for authentication.', 'LION' ),
					'type'        => 'string',
					'context'     => [ 'view', 'edit' ],
				],
				'smtp_secure'     => [
					'description' => __( 'SMTP encryption method.', 'LION' ),
					'type'        => 'string',
					'context'     => [ 'view', 'edit' ],
					'enum'        => [ '', 'ssl', 'tls' ],
				],
			],
		];

		return $this->add_additional_fields_schema( $schema );
	}
} 
