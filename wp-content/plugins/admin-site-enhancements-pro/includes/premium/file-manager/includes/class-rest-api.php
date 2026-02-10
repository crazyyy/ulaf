<?php
/**
 * REST API Class
 *
 * Handles REST API endpoints for file operations.
 *
 * @package ASENHA\FileManager
 */

namespace ASENHA\FileManager;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * REST API class
 */
class REST_API {

	/**
	 * API namespace.
	 *
	 * @var string
	 */
	private $namespace = 'asenha-file-manager/v1';

	/**
	 * Constructor - Register REST API routes.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register REST API routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		// Dismiss warning notice
		register_rest_route(
			$this->namespace,
			'/dismiss-warning',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'dismiss_warning' ),
				'permission_callback' => array( $this, 'check_permissions' ),
				'args'                => array(
					'csrf_token' => array(
						'required'          => false,
						'validate_callback' => array( $this, 'validate_csrf_token' ),
					),
				),
			)
		);
		
		// Get directory contents
		register_rest_route(
			$this->namespace,
			'/directory',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_directory' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		// Get directory tree
		register_rest_route(
			$this->namespace,
			'/tree',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_tree' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		// Read file
		register_rest_route(
			$this->namespace,
			'/file',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'read_file' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		// Create/Update file
		register_rest_route(
			$this->namespace,
			'/file',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'write_file' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		// Create folder
		register_rest_route(
			$this->namespace,
			'/folder',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'create_folder' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		// Rename
		register_rest_route(
			$this->namespace,
			'/rename',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rename' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		// Delete
		register_rest_route(
			$this->namespace,
			'/delete',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'delete' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		// Copy
		register_rest_route(
			$this->namespace,
			'/copy',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'copy' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		// Move
		register_rest_route(
			$this->namespace,
			'/move',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'move' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		// Change permissions
		register_rest_route(
			$this->namespace,
			'/permissions',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'change_permissions' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		// Compress
		register_rest_route(
			$this->namespace,
			'/compress',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'compress' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		// Extract
		register_rest_route(
			$this->namespace,
			'/extract',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'extract' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		// Upload
		register_rest_route(
			$this->namespace,
			'/upload',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'upload' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		// Validate PHP
		register_rest_route(
			$this->namespace,
			'/validate-php',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'validate_php' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);
	}

	/**
	 * Check user permissions.
	 *
	 * @return bool|WP_Error
	 */
	public function check_permissions() {
		// Check basic capability
		if ( ! \current_user_can( 'manage_options' ) ) {
			return false;
		}
		
		// Rate limiting to prevent IDOR enumeration attacks
		$rate_limit_check = $this->check_rate_limit();
		if ( \is_wp_error( $rate_limit_check ) ) {
			return $rate_limit_check;
		}
		
		// Additional CSRF protection: verify Origin/Referer headers
		$this->verify_request_origin();
		
		return true;
	}
	
	/**
	 * Check rate limit for API requests to prevent IDOR enumeration.
	 *
	 * @return bool|WP_Error
	 */
	private function check_rate_limit() {
		$user_id = \get_current_user_id();
		
		// Rate limit: max 200 requests per minute per user (increased for tree operations)
		$rate_limit_key = 'asenha_fm_rate_limit_' . $user_id;
		$requests = \get_transient( $rate_limit_key );
		
		if ( false === $requests ) {
			// First request in this minute
			\set_transient( $rate_limit_key, 1, MINUTE_IN_SECONDS );
			return true;
		}
		
		if ( $requests >= 200 ) {
			return new \WP_Error(
				'rate_limit_exceeded',
				\__( 'Rate limit exceeded. Please wait a moment before trying again.', 'admin-site-enhancements'),
				array( 'status' => 429 )
			);
		}
		
		// Increment request count
		\set_transient( $rate_limit_key, $requests + 1, MINUTE_IN_SECONDS );
		
		return true;
	}
	
	/**
	 * Check request throttling for specific operations (prevents rapid enumeration).
	 *
	 * @param string $operation Operation name.
	 * @param string $context   Optional context (e.g., path) to distinguish legitimate from suspicious requests.
	 * @return bool|WP_Error
	 */
	private function check_operation_throttle( $operation, $context = '' ) {
		$user_id = \get_current_user_id();
		
		// Use operation + context for throttle key to allow different paths/contexts
		$throttle_key = 'asenha_fm_throttle_' . $operation . '_' . md5( $context ) . '_' . $user_id;
		$last_request = \get_transient( $throttle_key );
		
		if ( false !== $last_request ) {
			$time_since = time() - $last_request;
			
			// Only throttle if less than 0.5 seconds (500ms) have passed
			// This allows normal UI operations while blocking rapid enumeration
			if ( $time_since < 0.5 ) {
				return new \WP_Error(
					'operation_throttled',
					\__( 'Operation throttled. Please slow down.', 'admin-site-enhancements'),
					array( 'status' => 429 )
				);
			}
		}
		
		// Set throttle with current timestamp
		\set_transient( $throttle_key, time(), 2 );
		
		return true;
	}
	
	/**
	 * Verify request origin to prevent CSRF attacks.
	 *
	 * @return bool|WP_Error
	 */
	private function verify_request_origin() {
		// Get the site URL
		$site_url = \get_site_url();
		$parsed_site = wp_parse_url( $site_url );
		$allowed_host = $parsed_site['host'];
		
		// Check Origin header first (preferred for AJAX requests)
		$origin = isset( $_SERVER['HTTP_ORIGIN'] ) ? \sanitize_text_field( \wp_unslash( $_SERVER['HTTP_ORIGIN'] ) ) : '';
		
		if ( ! empty( $origin ) ) {
			$parsed_origin = wp_parse_url( $origin );
			if ( isset( $parsed_origin['host'] ) && $parsed_origin['host'] !== $allowed_host ) {
				return new \WP_Error(
					'invalid_origin',
					\__( 'Invalid request origin.', 'admin-site-enhancements'),
					array( 'status' => 403 )
				);
			}
			return true;
		}
		
		// Fallback to Referer header
		$referer = isset( $_SERVER['HTTP_REFERER'] ) ? \sanitize_text_field( \wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
		
		if ( ! empty( $referer ) ) {
			$parsed_referer = wp_parse_url( $referer );
			if ( isset( $parsed_referer['host'] ) && $parsed_referer['host'] !== $allowed_host ) {
				return new \WP_Error(
					'invalid_referer',
					\__( 'Invalid request referer.', 'admin-site-enhancements'),
					array( 'status' => 403 )
				);
			}
			return true;
		}
		
		// If neither Origin nor Referer is present, it might be a direct API call
		// Allow it but log for monitoring (WordPress REST API nonce should still protect)
		return true;
	}
	
	/**
	 * Validate CSRF token for critical operations.
	 *
	 * @param mixed           $value   Token value.
	 * @param WP_REST_Request $request Request object.
	 * @param string          $param   Parameter name.
	 * @return bool
	 */
	public function validate_csrf_token( $value, $request, $param ) {
		// Get the endpoint being accessed
		$route = $request->get_route();
		
		// Critical operations that require additional CSRF token
		$critical_operations = array(
			'/asenha-file-manager/v1/delete',
			'/asenha-file-manager/v1/permissions',
			'/asenha-file-manager/v1/upload',
		);
		
		// Check if this is a critical operation
		$is_critical = false;
		foreach ( $critical_operations as $critical_route ) {
			if ( false !== strpos( $route, $critical_route ) ) {
				$is_critical = true;
				break;
			}
		}
		
		// If not a critical operation, skip additional token validation
		if ( ! $is_critical ) {
			return true;
		}
		
		// For critical operations, validate the custom CSRF token
		// Token format: base64(user_id:timestamp:hash)
		if ( empty( $value ) ) {
			// Generate and store a new token if none provided (first request)
			$user_id = \get_current_user_id();
			$timestamp = time();
			$hash = \wp_hash( $user_id . $timestamp . \wp_salt( 'nonce' ) );
			$token = base64_encode( $user_id . ':' . $timestamp . ':' . $hash );
			
			// Store token in transient (valid for 1 hour)
			\set_transient( 'asenha_fm_csrf_' . $user_id, $token, HOUR_IN_SECONDS );
			
			return false; // First request should fail and get new token
		}
		
		// Decode and validate the token
		$decoded = base64_decode( $value );
		$parts = explode( ':', $decoded );
		
		if ( 3 !== count( $parts ) ) {
			return false;
		}
		
		list( $token_user_id, $token_timestamp, $token_hash ) = $parts;
		
		// Validate user ID matches
		if ( (int) $token_user_id !== \get_current_user_id() ) {
			return false;
		}
		
		// Validate timestamp (token must be used within 1 hour)
		if ( ( time() - (int) $token_timestamp ) > HOUR_IN_SECONDS ) {
			return false;
		}
		
		// Validate hash
		$expected_hash = \wp_hash( $token_user_id . $token_timestamp . \wp_salt( 'nonce' ) );
		if ( ! \hash_equals( $expected_hash, $token_hash ) ) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Dismiss warning notice.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function dismiss_warning( WP_REST_Request $request ) {
		$user_id = \get_current_user_id();
		\update_user_meta( $user_id, 'asenha_fm_warning_dismissed', true );
		
		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => \__( 'Warning dismissed.', 'admin-site-enhancements'),
			),
			200
		);
	}

	/**
	 * Get directory contents.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_directory( WP_REST_Request $request ) {
		$path = $request->get_param( 'path' );

		if ( empty( $path ) ) {
			$path = ABSPATH;
		}

		// Apply operation throttling to prevent rapid directory enumeration
		// Use path as context to allow accessing different directories
		$throttle_check = $this->check_operation_throttle( 'get_directory', $path );
		if ( \is_wp_error( $throttle_check ) ) {
			return $throttle_check;
		}

		$contents = File_Operations::get_directory_contents( $path );

		if ( is_wp_error( $contents ) ) {
			// Log failed directory access attempts for monitoring
			$this->log_failed_access( 'directory', $path );
			
			return new WP_Error(
				$contents->get_error_code(),
				$contents->get_error_message(),
				array( 'status' => 400 )
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $contents,
				'path'    => $path,
			),
			200
		);
	}
	
	/**
	 * Log failed access attempts for security monitoring.
	 *
	 * @param string $type Access type (directory, file, etc.).
	 * @param string $path Attempted path.
	 * @return void
	 */
	private function log_failed_access( $type, $path ) {
		$user_id = \get_current_user_id();
		$log_key = 'asenha_fm_failed_access_' . $user_id;
		
		// Get existing log
		$log = \get_transient( $log_key );
		if ( false === $log ) {
			$log = array();
		}
		
		// Add new entry
		$log[] = array(
			'type'      => $type,
			'path'      => $path,
			'timestamp' => time(),
			'ip'        => isset( $_SERVER['REMOTE_ADDR'] ) ? \sanitize_text_field( \wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
		);
		
		// Keep only last 10 failed attempts
		if ( count( $log ) > 10 ) {
			$log = array_slice( $log, -10 );
		}
		
		// Store for 1 hour
		\set_transient( $log_key, $log, HOUR_IN_SECONDS );
		
		// If more than 5 failed attempts in last hour, it might be an attack
		if ( count( $log ) > 5 ) {
			// Could send admin notification or take other action
			\do_action( 'asenha_fm_suspicious_activity', $user_id, $log );
		}
	}

	/**
	 * Get directory tree.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_tree( WP_REST_Request $request ) {
		$path = $request->get_param( 'path' );
		$depth = $request->get_param( 'depth' );
		$current_path = $request->get_param( 'currentPath' );

		if ( empty( $path ) ) {
			$path = ABSPATH;
		}

		if ( empty( $depth ) ) {
			$depth = 1;
		}

		// Pass currentPath to build_tree for server-side expansion
		$tree = File_Operations::build_tree( $path, (int) $depth, $current_path );

		if ( is_wp_error( $tree ) ) {
			return new WP_Error(
				$tree->get_error_code(),
				$tree->get_error_message(),
				array( 'status' => 400 )
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $tree,
			),
			200
		);
	}

	/**
	 * Read file contents.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function read_file( WP_REST_Request $request ) {
		$path = $request->get_param( 'path' );

		if ( empty( $path ) ) {
			return new WP_Error( 'missing_path', __( 'Path parameter is required.', 'admin-site-enhancements'), array( 'status' => 400 ) );
		}

		// Apply operation throttling to prevent rapid file enumeration
		// Use path as context to allow reading different files
		$throttle_check = $this->check_operation_throttle( 'read_file', $path );
		if ( \is_wp_error( $throttle_check ) ) {
			return $throttle_check;
		}

		$contents = File_Operations::read_file( $path );

		if ( is_wp_error( $contents ) ) {
			// Log failed file access attempts
			$this->log_failed_access( 'file', $path );
			
			return new WP_Error(
				$contents->get_error_code(),
				$contents->get_error_message(),
				array( 'status' => 400 )
			);
		}

		// Get file info to check if protected
		$file_info = File_Operations::get_file_info( $path );
		$is_protected = ! is_wp_error( $file_info ) && isset( $file_info['is_protected'] ) ? $file_info['is_protected'] : false;

		// Determine whether the file should be treated as read-only by DISALLOW_* constants.
		$read_only_meta = array(
			'is_read_only_by_constants' => false,
			'read_only_reason'          => null,
		);
		if ( ! is_wp_error( $file_info ) && isset( $file_info['path'] ) ) {
			$read_only_meta = File_Operations::get_read_only_meta_by_constants( $file_info['path'] );
		}

		return new WP_REST_Response(
			array(
				'success'   => true,
				'data'      => $contents,
				'path'      => $path,
				'data_meta' => array(
					'is_protected' => $is_protected,
					'is_read_only_by_constants' => (bool) $read_only_meta['is_read_only_by_constants'],
					'read_only_reason'          => $read_only_meta['read_only_reason'],
				),
			),
			200
		);
	}

	/**
	 * Write file contents.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function write_file( WP_REST_Request $request ) {
		$path = $request->get_param( 'path' );
		$contents = $request->get_param( 'contents' );
		$create = $request->get_param( 'create' );

		if ( empty( $path ) ) {
			return new WP_Error( 'missing_path', __( 'Path parameter is required.', 'admin-site-enhancements'), array( 'status' => 400 ) );
		}

		// If creating a new file
		if ( $create && ! file_exists( $path ) ) {
			$result = File_Operations::create_file( $path );
			if ( is_wp_error( $result ) ) {
				return new WP_Error(
					$result->get_error_code(),
					$result->get_error_message(),
					array( 'status' => 400 )
				);
			}
		}

		$result = File_Operations::write_file( $path, $contents );

		if ( is_wp_error( $result ) ) {
			return new WP_Error(
				$result->get_error_code(),
				$result->get_error_message(),
				array( 'status' => 400 )
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'File saved successfully.', 'admin-site-enhancements'),
			),
			200
		);
	}

	/**
	 * Create folder.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_folder( WP_REST_Request $request ) {
		$path = $request->get_param( 'path' );

		if ( empty( $path ) ) {
			return new WP_Error( 'missing_path', __( 'Path parameter is required.', 'admin-site-enhancements'), array( 'status' => 400 ) );
		}

		$result = File_Operations::create_folder( $path );

		if ( is_wp_error( $result ) ) {
			return new WP_Error(
				$result->get_error_code(),
				$result->get_error_message(),
				array( 'status' => 400 )
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Folder created successfully.', 'admin-site-enhancements'),
			),
			200
		);
	}

	/**
	 * Rename file or folder.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function rename( WP_REST_Request $request ) {
		$old_path = $request->get_param( 'old_path' );
		$new_path = $request->get_param( 'new_path' );

		if ( empty( $old_path ) || empty( $new_path ) ) {
			return new WP_Error( 'missing_params', __( 'Both old_path and new_path parameters are required.', 'admin-site-enhancements'), array( 'status' => 400 ) );
		}

		$result = File_Operations::rename( $old_path, $new_path );

		if ( is_wp_error( $result ) ) {
			return new WP_Error(
				$result->get_error_code(),
				$result->get_error_message(),
				array( 'status' => 400 )
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Renamed successfully.', 'admin-site-enhancements'),
			),
			200
		);
	}

	/**
	 * Delete file or folder.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete( WP_REST_Request $request ) {
		$paths = $request->get_param( 'paths' );

		if ( empty( $paths ) || ! is_array( $paths ) ) {
			return new WP_Error( 'missing_paths', __( 'Paths parameter is required and must be an array.', 'admin-site-enhancements'), array( 'status' => 400 ) );
		}

		$errors = array();
		$success_count = 0;

		foreach ( $paths as $path ) {
			$result = File_Operations::delete( $path );

			if ( is_wp_error( $result ) ) {
				$errors[] = array(
					'path'    => $path,
					'message' => $result->get_error_message(),
				);
			} else {
				++$success_count;
			}
		}

		if ( ! empty( $errors ) && 0 === $success_count ) {
			return new WP_Error(
				'delete_failed',
				__( 'Failed to delete items.', 'admin-site-enhancements'),
				array(
					'status' => 400,
					'errors' => $errors,
				)
			);
		}

		return new WP_REST_Response(
			array(
				'success'       => true,
				'message'       => __( 'Items deleted successfully.', 'admin-site-enhancements'),
				'success_count' => $success_count,
				'errors'        => $errors,
			),
			200
		);
	}

	/**
	 * Copy file or folder.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function copy( WP_REST_Request $request ) {
		$source = $request->get_param( 'source' );
		$destination = $request->get_param( 'destination' );

		if ( empty( $source ) || empty( $destination ) ) {
			return new WP_Error( 'missing_params', __( 'Both source and destination parameters are required.', 'admin-site-enhancements'), array( 'status' => 400 ) );
		}

		$result = File_Operations::copy( $source, $destination );

		if ( is_wp_error( $result ) ) {
			return new WP_Error(
				$result->get_error_code(),
				$result->get_error_message(),
				array( 'status' => 400 )
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Copied successfully.', 'admin-site-enhancements'),
			),
			200
		);
	}

	/**
	 * Move file or folder.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function move( WP_REST_Request $request ) {
		$source = $request->get_param( 'source' );
		$destination = $request->get_param( 'destination' );

		if ( empty( $source ) || empty( $destination ) ) {
			return new WP_Error( 'missing_params', __( 'Both source and destination parameters are required.', 'admin-site-enhancements'), array( 'status' => 400 ) );
		}

		// Move is essentially rename
		$result = File_Operations::rename( $source, $destination );

		if ( is_wp_error( $result ) ) {
			return new WP_Error(
				$result->get_error_code(),
				$result->get_error_message(),
				array( 'status' => 400 )
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Moved successfully.', 'admin-site-enhancements'),
			),
			200
		);
	}

	/**
	 * Change file permissions.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function change_permissions( WP_REST_Request $request ) {
		$path = $request->get_param( 'path' );
		$permissions = $request->get_param( 'permissions' );

		if ( empty( $path ) || empty( $permissions ) ) {
			return new WP_Error( 'missing_params', __( 'Both path and permissions parameters are required.', 'admin-site-enhancements'), array( 'status' => 400 ) );
		}

		$result = File_Operations::change_permissions( $path, $permissions );

		if ( is_wp_error( $result ) ) {
			return new WP_Error(
				$result->get_error_code(),
				$result->get_error_message(),
				array( 'status' => 400 )
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Permissions changed successfully.', 'admin-site-enhancements'),
			),
			200
		);
	}

	/**
	 * Compress files/folders.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function compress( WP_REST_Request $request ) {
		$paths = $request->get_param( 'paths' );
		$destination = $request->get_param( 'destination' );
		$archive_name = $request->get_param( 'archive_name' );

		if ( empty( $paths ) || ! is_array( $paths ) ) {
			return new WP_Error( 'missing_paths', __( 'Paths parameter is required and must be an array.', 'admin-site-enhancements'), array( 'status' => 400 ) );
		}

		if ( empty( $destination ) ) {
			return new WP_Error( 'missing_destination', __( 'Destination parameter is required.', 'admin-site-enhancements'), array( 'status' => 400 ) );
		}

		if ( empty( $archive_name ) ) {
			$archive_name = 'archive-' . gmdate( 'Y-m-d-His' ) . '.zip';
		}

		$result = File_Operations::compress( $paths, $archive_name, $destination );

		if ( is_wp_error( $result ) ) {
			return new WP_Error(
				$result->get_error_code(),
				$result->get_error_message(),
				array( 'status' => 400 )
			);
		}

		return new WP_REST_Response(
			array(
				'success'      => true,
				'message'      => __( 'Archive created successfully.', 'admin-site-enhancements'),
				'archive_path' => $result,
			),
			200
		);
	}

	/**
	 * Extract archive.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function extract( WP_REST_Request $request ) {
		$archive_path = $request->get_param( 'archive_path' );
		$destination = $request->get_param( 'destination' );

		if ( empty( $archive_path ) ) {
			return new WP_Error( 'missing_archive', __( 'Archive path parameter is required.', 'admin-site-enhancements'), array( 'status' => 400 ) );
		}

		if ( empty( $destination ) ) {
			return new WP_Error( 'missing_destination', __( 'Destination parameter is required.', 'admin-site-enhancements'), array( 'status' => 400 ) );
		}

		$result = File_Operations::extract( $archive_path, $destination );

		if ( is_wp_error( $result ) ) {
			return new WP_Error(
				$result->get_error_code(),
				$result->get_error_message(),
				array( 'status' => 400 )
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Archive extracted successfully.', 'admin-site-enhancements'),
			),
			200
		);
	}

	/**
	 * Handle file upload.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function upload( WP_REST_Request $request ) {
		$destination = $request->get_param( 'destination' );

		if ( empty( $destination ) ) {
			return new WP_Error( 'missing_destination', \__( 'Destination parameter is required.', 'admin-site-enhancements'), array( 'status' => 400 ) );
		}

		// Validate destination path
		$validated_dest = File_Operations::validate_path( $destination );
		if ( \is_wp_error( $validated_dest ) ) {
			return new WP_Error(
				$validated_dest->get_error_code(),
				$validated_dest->get_error_message(),
				array( 'status' => 400 )
			);
		}

		// Enforce DISALLOW_* restrictions (read-only by constants).
		$restriction_error = File_Operations::get_write_restriction_error_by_constants_for_path( $validated_dest );
		if ( \is_wp_error( $restriction_error ) ) {
			return new WP_Error(
				$restriction_error->get_error_code(),
				$restriction_error->get_error_message(),
				array( 'status' => 403 )
			);
		} elseif ( $restriction_error ) {
			return new WP_Error(
				$restriction_error->get_error_code(),
				$restriction_error->get_error_message(),
				array( 'status' => 403 )
			);
		}

		// Note: We don't need to check is_writable here as File_Operations methods
		// will handle filesystem checks internally via WP_Filesystem
		if ( ! \is_dir( $validated_dest ) ) {
			return new WP_Error( 'not_directory', \__( 'Destination path is not a directory.', 'admin-site-enhancements'), array( 'status' => 400 ) );
		}

		// Disallow uploads into WordPress core folders.
		if ( File_Operations::is_protected_core_write_folder( $validated_dest ) ) {
			return new WP_Error(
				'protected_core_folder',
				\__( 'Modifying WordPress core folders is not allowed.', 'admin-site-enhancements' ),
				array( 'status' => 403 )
			);
		}

		// Check if files were uploaded
		$files = $request->get_file_params();
		if ( empty( $files ) ) {
			return new WP_Error( 'no_files', \__( 'No files were uploaded.', 'admin-site-enhancements'), array( 'status' => 400 ) );
		}
		
		// Load WordPress file handling functions (required for wp_handle_upload)
		if ( ! \function_exists( 'wp_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		$uploaded_files = array();
		$errors = array();
		
		// Handle files array - convert from PHP's multi-file array format to individual file arrays
		$files_to_upload = array();
		
		if ( isset( $files['files'] ) && \is_array( $files['files']['name'] ) ) {
			// Multiple files uploaded with files[] notation
			$file_count = \count( $files['files']['name'] );
			for ( $i = 0; $i < $file_count; $i++ ) {
				$files_to_upload[] = array(
					'name'     => $files['files']['name'][ $i ],
					'type'     => $files['files']['type'][ $i ],
					'tmp_name' => $files['files']['tmp_name'][ $i ],
					'error'    => $files['files']['error'][ $i ],
					'size'     => $files['files']['size'][ $i ],
				);
			}
		} else {
			// Single file or individually named files
			$files_to_upload = $files;
		}

		foreach ( $files_to_upload as $file ) {
			// MIME type validation - don't trust client-provided MIME type
			// Use fileinfo to detect actual MIME type from file content
			if ( isset( $file['tmp_name'] ) && \file_exists( $file['tmp_name'] ) ) {
				// Get actual MIME type from file content
				$finfo = \finfo_open( FILEINFO_MIME_TYPE );
				$actual_mime = \finfo_file( $finfo, $file['tmp_name'] );
				\finfo_close( $finfo );
				
				// Whitelist of allowed MIME types
				$allowed_mimes = array(
					// Images
					'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
					// Documents
					'application/pdf', 'application/msword', 
					'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
					'application/vnd.ms-excel',
					'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
					'application/vnd.ms-powerpoint',
					'application/vnd.openxmlformats-officedocument.presentationml.presentation',
					// Text files
					'text/plain', 'text/html', 'text/css', 'text/javascript',
					'application/javascript', 'application/json', 'application/xml', 'text/xml',
					// Code files (text-based)
					'text/x-php', 'application/x-php', 'text/x-python', 'text/x-shellscript',
					// Archives
					'application/zip', 'application/x-zip-compressed',
					'application/x-gzip', 'application/x-tar',
					// Video/Audio (optional - can be commented out for stricter security)
					'video/mp4', 'video/mpeg', 'video/quicktime', 'video/webm',
					'audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/ogg',
					// Other
					'application/octet-stream', // Binary files (be cautious)
				);
				
				// Additional check: if MIME is generic octet-stream, check extension
				if ( 'application/octet-stream' === $actual_mime || ! $actual_mime ) {
					// Fallback to extension-based validation for binary files
					$extension = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
					$allowed_extensions = array(
						'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg',
						'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
						'txt', 'html', 'htm', 'css', 'js', 'json', 'xml',
						'php', 'py', 'sh', 'bash',
						'zip', 'tar', 'gz',
						'mp4', 'mpeg', 'mov', 'webm', 'mp3', 'wav', 'ogg',
						'md', 'log', 'sql', 'ini', 'conf', 'yaml', 'yml',
					);
					
					if ( ! in_array( $extension, $allowed_extensions, true ) ) {
						$errors[] = array(
							'file'    => $file['name'],
							'message' => sprintf(
								/* translators: 1: file extension, 2: file name */
								\__( 'File extension ".%1$s" is not allowed. To allow this file type, add it to WordPress allowed MIME types using the "upload_mimes" filter in your theme\'s functions.php or a custom plugin.', 'admin-site-enhancements'),
								$extension
							),
						);
						continue;
					}
				} elseif ( ! in_array( $actual_mime, $allowed_mimes, true ) ) {
					$extension = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
					$errors[] = array(
						'file'    => $file['name'],
						'message' => sprintf(
							/* translators: 1: MIME type, 2: file extension */
							\__( 'File type "%1$s" (.%2$s) is not allowed. To allow this file type, add its MIME type to WordPress allowed types using the "upload_mimes" filter. Example: add_filter(\'upload_mimes\', function($mimes) { $mimes[\'%2$s\'] = \'%1$s\'; return $mimes; });', 'admin-site-enhancements'),
							$actual_mime,
							$extension
						),
					);
					continue;
				}
				
				// Prevent uploading executable files by checking file content
				// This prevents bypassing MIME checks with polyglot files
				$file_content_start = \file_get_contents( $file['tmp_name'], false, null, 0, 1024 );
				$dangerous_signatures = array(
					'MZ',               // Windows executable
					"\x7fELF",          // Linux executable
					'#!',               // Script shebang
					'<?php',            // PHP code (if not explicitly allowed)
					'<%',               // ASP/JSP code
				);
				
				foreach ( $dangerous_signatures as $signature ) {
					if ( 0 === strpos( $file_content_start, $signature ) ) {
						// Allow PHP files if they have php extension
						$extension = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
						if ( '<?php' === $signature && 'php' === $extension ) {
							// PHP files are allowed
							continue;
						}
						
						$errors[] = array(
							'file'    => $file['name'],
							'message' => \__( 'File contains executable code and cannot be uploaded for security reasons.', 'admin-site-enhancements'),
						);
						continue 2; // Skip to next file
					}
				}
			}

			// Override upload directory
			\add_filter( 'upload_dir', function( $upload ) use ( $validated_dest ) {
				return array(
					'path'   => $validated_dest,
					'url'    => '',
					'subdir' => '',
					'basedir' => $validated_dest,
					'baseurl' => '',
					'error'  => false,
				);
			} );

			$uploaded = \wp_handle_upload(
				$file,
				array(
					'test_form' => false,
					'unique_filename_callback' => function( $dir, $name, $ext ) {
						return $name;
					},
				)
			);

			\remove_all_filters( 'upload_dir' );

			if ( isset( $uploaded['error'] ) ) {
				$errors[] = array(
					'file'    => $file['name'],
					'message' => $uploaded['error'],
				);
			} else {
				// After upload, verify the file permissions are safe (no executable bit)
				if ( isset( $uploaded['file'] ) && \file_exists( $uploaded['file'] ) ) {
					// Ensure uploaded file doesn't have executable permissions
					\chmod( $uploaded['file'], 0644 ); // rw-r--r--
				}
				$uploaded_files[] = $uploaded['file'];
			}
		}

		if ( ! empty( $errors ) && empty( $uploaded_files ) ) {
			return new WP_Error(
				'upload_failed',
				\__( 'Failed to upload files.', 'admin-site-enhancements'),
				array(
					'status' => 400,
					'errors' => $errors,
				)
			);
		}

		return new WP_REST_Response(
			array(
				'success'        => true,
				'message'        => \__( 'Files uploaded successfully.', 'admin-site-enhancements'),
				'uploaded_files' => $uploaded_files,
				'errors'         => $errors,
			),
			200
		);
	}

	/**
	 * Validate PHP syntax.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function validate_php( WP_REST_Request $request ) {
		$code = $request->get_param( 'code' );

		if ( empty( $code ) ) {
			return new WP_Error( 'missing_code', __( 'Code parameter is required.', 'admin-site-enhancements'), array( 'status' => 400 ) );
		}

		$result = File_Operations::validate_php_syntax( $code );

		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response(
				array(
					'valid'   => false,
					'message' => $result->get_error_message(),
				),
				200
			);
		}

		return new WP_REST_Response(
			array(
				'valid'   => true,
				'message' => __( 'PHP syntax is valid.', 'admin-site-enhancements'),
			),
			200
		);
	}
}

