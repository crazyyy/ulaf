<?php
/**
 * REST Controller class for handling email logs.
 *
 * @package Solid_SMTP\Controller
 */

namespace SolidWP\Mail\Admin\REST;

use SolidWP\Mail\Helper\Rest;
use SolidWP\Mail\Repository\LogsRepository;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Class Logs
 *
 * Handles REST requests for fetching and searching email logs.
 */
class Logs extends \WP_REST_Controller {

	/**
	 * The namespace for the REST API endpoints.
	 *
	 * @var string
	 */
	protected $namespace = 'solidwp-mail/v1';

	/**
	 * The resource name for the REST API endpoints.
	 *
	 * @var string
	 */
	protected $rest_base = 'logs';

	/**
	 * The repository for handling email logs.
	 *
	 * @var LogsRepository
	 */
	protected LogsRepository $repository;

	/**
	 * Constructor.
	 *
	 * @param LogsRepository $repository The repository for handling email logs.
	 */
	public function __construct( LogsRepository $repository ) {
		$this->repository = $repository;
	}

	/**
	 * Registers the REST routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_items' ],
				'permission_callback' => [ $this, 'get_items_permissions_check' ],
				'args'                => $this->get_collection_params(),
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				'methods'             => \WP_REST_Server::DELETABLE,
				'callback'            => [ $this, 'clear_all_logs' ],
				'permission_callback' => [ $this, 'get_items_permissions_check' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/delete',
			[
				'methods'             => \WP_REST_Server::DELETABLE,
				'callback'            => [ $this, 'delete_item' ],
				'permission_callback' => [ $this, 'get_items_permissions_check' ],
				'args'                => $this->get_collection_params(),
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/export-csv',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'export_csv' ],
				'permission_callback' => [ $this, 'get_items_permissions_check' ],
				'args'                => [
					'date_from' => [
						'required'          => true,
						'type'              => 'string',
						'format'            => 'date',
						'sanitize_callback' => 'sanitize_text_field',
					],
					'date_to'   => [
						'required'          => true,
						'type'              => 'string',
						'format'            => 'date',
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
			]
		);
	}

	/**
	 * Handles the REST request for exporting email logs as CSV.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response|WP_Error The response or WP_Error object on failure.
	 */
	public function export_csv( $request ) {
		$date_from = $request->get_param( 'date_from' );
		$date_to   = $request->get_param( 'date_to' );

		// Validate date format (optional)
		if ( ! $this->is_valid_date( $date_from ) || ! $this->is_valid_date( $date_to ) ) {
			return new WP_Error( 'invalid_date', __( 'Invalid date format', 'wp-smtp' ), [ 'status' => 400 ] );
		}

		// Query logs within the date range
		$logs = $this->repository->get_email_logs_by_date( strtotime( $date_from ), strtotime( $date_to ) );
		// Prepare CSV content
		$csv_content = $this->generate_csv_content( $logs );

		// Set CSV filename
		$csv_filename = 'solid-mail-logs-' . $date_from . '-to-' . $date_to . '.csv';

		// Serve CSV file
		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $csv_filename ) . '"' );
		echo $csv_content; // phpcs:ignore StellarWP.XSS.EscapeOutput.OutputNotEscaped, WordPress.Security.EscapeOutput.OutputNotEscaped

		exit;
	}

	/**
	 * Validates the date format.
	 *
	 * @param string $date The date string to validate.
	 *
	 * @return bool True if valid, false otherwise.
	 */
	private function is_valid_date( $date ) {
		$d = \DateTime::createFromFormat( 'Y-m-d', $date );

		return $d && $d->format( 'Y-m-d' ) === $date;
	}

	/**
	 * Generates CSV content from logs data.
	 *
	 * @param array $logs The logs data.
	 *
	 * @return string The generated CSV content.
	 */
	private function generate_csv_content( $logs ) {
		$csv_output = fopen( 'php://output', 'w' );

		// Add CSV headers
		fputcsv( $csv_output, [ 'To', 'Timestamp', 'Subject', 'Error' ] );

		// Add CSV rows
		foreach ( $logs as $log ) {
			fputcsv( $csv_output, [ implode( ' ', $log['to'] ), $log['timestamp'], $log['subject'], $log['error'] ] );
		}

		fclose( $csv_output );

		// Capture CSV output
		return ob_get_clean();
	}

	/**
	 * Checks if a given request has access to perform the action.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool True if the request has access, WP_Error otherwise.
	 */
	public function get_items_permissions_check( $request ) {
		// Only allow access for administrators.
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'rest_cannot_view', __( 'You do not have permissions to access this endpoint', 'wp-smtp' ), [ 'status' => rest_authorization_required_code() ] );
		}

		return true;
	}

	/**
	 * Retrieves the collection parameters for the logs endpoint.
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params(): array {
		return [
			'page'        => [
				'required'          => true,
				'type'              => 'integer',
				'default'           => 0,
				'sanitize_callback' => 'absint',
			],
			'sortby'      => [
				'required' => false,
				'type'     => 'string',
				'default'  => 'timestamp',
				'enum'     => [ 'timestamp', 'subject', 'to' ],
			],
			'sort'        => [
				'required' => false,
				'type'     => 'string',
				'default'  => 'desc',
				'enum'     => [ 'asc', 'desc' ],
			],
			'search_term' => [
				'required'          => false,
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'per_page'    => [
				'required'          => false,
				'type'              => 'integer',
				'default'           => 10,
				'sanitize_callback' => 'absint',
			],
			'logIds'      => [
				'required'          => true,
				'default'           => [],
				'type'              => 'array',
				'sanitize_callback' => 'wp_parse_id_list',
			],
		];
	}

	/**
	 * Handles the REST request for deleting email logs.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response|WP_Error The response or WP_Error object on failure.
	 */
	public function delete_item( $request ) {
		$log_ids = $request['logIds'];
		$this->repository->delete_logs( $log_ids );

		return new WP_REST_Response( null, \WP_Http::NO_CONTENT );
	}

	/**
	 * Handles the REST request for clearing all email logs.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response|WP_Error The response or WP_Error object on failure.
	 */
	public function clear_all_logs( $request ) {
		$result = $this->repository->clear_all_logs();

		if ( ! $result ) {
			return new WP_Error( 'clear_logs_failed', __( 'Failed to clear all logs.', 'wp-smtp' ), [ 'status' => 500 ] );
		}

		return new WP_REST_Response( null, \WP_Http::NO_CONTENT );
	}

	/**
	 * Handles the REST request for fetching email logs.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response|WP_Error The response or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		$search_term = $request->get_param( 'search_term' );

		$total = $this->repository->count_all_logs( $search_term );
		$data  = [
			'logs' => $this->repository->get_email_logs( $request->get_params() ),
		];

		$response = rest_ensure_response( $data );
		Rest::paginate( $request, $response, $total, $this->namespace . '/' . $this->rest_base );

		return $response;
	}
}
