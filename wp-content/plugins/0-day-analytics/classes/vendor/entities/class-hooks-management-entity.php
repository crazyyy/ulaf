<?php
/**
 * Entity: Hooks Management.
 *
 * @package advan
 *
 * @since 4.5.0
 */

declare(strict_types=1);

namespace ADVAN\Entities;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\ADVAN\Entities\Hooks_Management_Entity' ) ) {
	/**
	 * Responsible for managing which hooks to capture.
	 */
	class Hooks_Management_Entity extends Abstract_Entity {
		/**
		 * Contains the table name.
		 *
		 * @var string
		 *
		 * @since 4.5.0
		 */
		protected static $table = ADVAN_PREFIX . 'hooks_management';

		/**
		 * Keeps the info about the columns of the table - name, type.
		 *
		 * @var array
		 *
		 * @since 4.5.0
		 */
		protected static $fields = array(
			'id'              => 'int',
			'hook_name'       => 'string',
			'hook_label'      => 'string',
			'hook_type'       => 'string',
			'priority'        => 'int',
			'enabled'         => 'int',
			'capture_args'    => 'int',
			'capture_output'  => 'int',
			'description'     => 'string',
			'category'        => 'string',
			'hook_parameters' => 'string',
			'group_id'        => 'int',
			'date_added'      => 'float',
			'date_modified'   => 'float',
		);

		/**
		 * Holds all the default values for the columns.
		 *
		 * @var array
		 *
		 * @since 4.5.0
		 */
		protected static $fields_values = array(
			'id'              => 0,
			'hook_name'       => '',
			'hook_label'      => '',
			'hook_type'       => 'action',
			'priority'        => 10,
			'enabled'         => 1,
			'capture_args'    => 1,
			'capture_output'  => 1,
			'description'     => '',
			'category'        => 'custom',
			'hook_parameters' => '',
			'group_id'        => 0,
			'date_added'      => 0.0,
			'date_modified'   => 0.0,
		);

		/**
		 * Creates table functionality.
		 *
		 * @param \wpdb $connection - \wpdb connection to be used for name extraction.
		 *
		 * @since 4.5.0
		 */
		public static function create_table( $connection = null ): bool {
			if ( null !== $connection ) {
				if ( $connection instanceof \wpdb ) {
					$collate = $connection->get_charset_collate();
				}
			} else {
				$collate = self::get_connection()->get_charset_collate();
			}
			$table_name = self::get_table_name( $connection );

			// Defensive validation of table name to avoid unexpected identifier injection.
			if ( ! is_string( $table_name ) || ! preg_match( '/^[a-zA-Z0-9_]+$/', $table_name ) ) {
				return false;
			}

			$wp_entity_sql = '
				CREATE TABLE `' . $table_name . '` (
					id BIGINT unsigned NOT NULL AUTO_INCREMENT,
					hook_name VARCHAR(191) NOT NULL DEFAULT "",
					hook_label VARCHAR(191) NOT NULL DEFAULT "",
					hook_type VARCHAR(10) NOT NULL DEFAULT "action",
					priority INT NOT NULL DEFAULT 10,
					enabled TINYINT(1) NOT NULL DEFAULT 1,
					capture_args TINYINT(1) NOT NULL DEFAULT 1,
					capture_output TINYINT(1) NOT NULL DEFAULT 1,
					description TEXT,
				category VARCHAR(50) NOT NULL DEFAULT "custom",
				hook_parameters LONGTEXT,
				group_id BIGINT unsigned NOT NULL DEFAULT 0,
				date_added DOUBLE NOT NULL DEFAULT 0,
					date_modified DOUBLE NOT NULL DEFAULT 0,
				PRIMARY KEY (id),
				UNIQUE KEY `hook_name_type` (`hook_name`, `hook_type`),
				KEY `enabled` (`enabled`),
				KEY `category` (`category`),
				KEY `group_id` (`group_id`)
				)
			  ' . $collate . ';';

			$result = self::maybe_create_table( $table_name, $wp_entity_sql, $connection );
			self::add_default_hooks();

			return $result;
		}

		/**
		 * Add default hooks to monitor.
		 *
		 * @return void
		 *
		 * @since 4.5.0
		 */
		private static function add_default_hooks() {
			$default_hooks = array(
				// Authentication hooks.
				array(
					'hook_name'       => 'wp_login',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1, // Enabled by default - non-aggressive.
					'capture_args'    => 2,
					'capture_output'  => 0,
					'hook_label'      => __( 'User logged in', 'advanced-analytics' ),
					'description'     => __( 'Fires after a user has logged in', 'advanced-analytics' ),
					'category'        => 'auth',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'user_login',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'user',
								'type'            => 'wp_user',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'wp_logout',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'User logged out', 'advanced-analytics' ),
					'description'     => __( 'Fires after a user has logged out', 'advanced-analytics' ),
					'category'        => 'auth',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'user_id',
								'type'            => 'user_id',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'wp_login_errors',
					'hook_type'       => 'filter',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Login errors', 'advanced-analytics' ),
					'description'     => __( 'Filter applied to login errors (WP_Error object).', 'advanced-analytics' ),
					'category'        => 'auth',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'errors',
								'type'            => 'wp_error',
								'extraction_code' => '',
							),
							array(
								'name'            => 'redirect_to',
								'type'            => 'string',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'wp_login_failed',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Login failed', 'advanced-analytics' ),
					'description'     => __( 'Fires when a login attempt fails', 'advanced-analytics' ),
					'category'        => 'auth',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'username',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'error',
								'type'            => 'wp_error',
								'extraction_code' => '',
							),
						)
					),
				),
				// Additional auth hooks
				array(
					'hook_name'       => 'authenticate',
					'hook_type'       => 'filter',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 3,
					'capture_output'  => 1,
					'hook_label'      => __( 'User authentication', 'advanced-analytics' ),
					'description'     => __( 'Filter to authenticate user credentials', 'advanced-analytics' ),
					'category'        => 'auth',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'user',
								'type'            => 'wp_user',
								'extraction_code' => '',
							),
							array(
								'name'            => 'username',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'password',
								'type'            => 'string',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'auth_cookie_malformed',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 0,
					'capture_args'    => 2,
					'capture_output'  => 0,
					'hook_label'      => __( 'Auth cookie malformed', 'advanced-analytics' ),
					'description'     => __( 'Fires when an auth cookie is malformed', 'advanced-analytics' ),
					'category'        => 'auth',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'cookie_elements',
								'type'            => 'array',
								'extraction_code' => '',
							),
							array(
								'name'            => 'scheme',
								'type'            => 'string',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'auth_cookie_valid',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 0,
					'capture_args'    => 2,
					'capture_output'  => 0,
					'hook_label'      => __( 'Auth cookie valid', 'advanced-analytics' ),
					'description'     => __( 'Fires when an auth cookie is valid', 'advanced-analytics' ),
					'category'        => 'auth',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'cookie_elements',
								'type'            => 'array',
								'extraction_code' => '',
							),
							array(
								'name'            => 'user',
								'type'            => 'wp_user',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'auth_cookie_bad_username',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Auth cookie bad username', 'advanced-analytics' ),
					'description'     => __( 'Fires when an auth cookie has a bad username', 'advanced-analytics' ),
					'category'        => 'auth',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'cookie_elements',
								'type'            => 'array',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'auth_cookie_bad_hash',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 0,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Auth cookie bad hash', 'advanced-analytics' ),
					'description'     => __( 'Fires when an auth cookie has a bad hash', 'advanced-analytics' ),
					'category'        => 'auth',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'cookie_elements',
								'type'            => 'array',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'auth_cookie_expired',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Auth cookie expired', 'advanced-analytics' ),
					'description'     => __( 'Fires when an auth cookie is expired', 'advanced-analytics' ),
					'category'        => 'auth',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'cookie_elements',
								'type'            => 'array',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'auth_redirect',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 0,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Auth redirect', 'advanced-analytics' ),
					'description'     => __( 'Fires before redirecting to the login page', 'advanced-analytics' ),
					'category'        => 'auth',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'user_id',
								'type'            => 'user_id',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'set_auth_cookie',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 6,
					'capture_output'  => 0,
					'hook_label'      => __( 'Set auth cookie', 'advanced-analytics' ),
					'description'     => __( 'Fires immediately before the authentication cookie is set', 'advanced-analytics' ),
					'category'        => 'auth',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'auth_cookie',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'expire',
								'type'            => 'int',
								'extraction_code' => '',
							),
							array(
								'name'            => 'expiration',
								'type'            => 'int',
								'extraction_code' => '',
							),
							array(
								'name'            => 'user_id',
								'type'            => 'user_id',
								'extraction_code' => '',
							),
							array(
								'name'            => 'scheme',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'token',
								'type'            => 'string',
								'extraction_code' => '',
							),
						)
					),
				),
				// User hooks.
				array(
					'hook_name'       => 'user_register',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'User registered', 'advanced-analytics' ),
					'description'     => __( 'Fires after a new user is registered', 'advanced-analytics' ),
					'category'        => 'user',
					'hook_parameters' => '',
				),
				array(
					'hook_name'       => 'delete_user',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'User deleted', 'advanced-analytics' ),
					'description'     => __( 'Fires when a user is deleted', 'advanced-analytics' ),
					'category'        => 'user',
					'hook_parameters' => '',
				),
				array(
					'hook_name'       => 'profile_update',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Profile updated', 'advanced-analytics' ),
					'description'     => __( 'Fires immediately after an existing user is updated', 'advanced-analytics' ),
					'category'        => 'user',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'user_id',
								'type'            => 'user_id',
								'extraction_code' => '',
							),
							array(
								'name'            => 'old_user_data',
								'type'            => 'wp_user',
								'extraction_code' => '',
							),
						)
					),
				),

				// Post hooks.
				// Extended user and multisite hooks.
				array(
					'hook_name'       => 'set_user_role',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'User role set', 'advanced-analytics' ),
					'description'     => __( 'Fires when a user role is set (role change).', 'advanced-analytics' ),
					'category'        => 'user',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'user_id',
								'type'            => 'user_id',
								'extraction_code' => '',
							),
							array(
								'name'            => 'role',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'old_roles',
								'type'            => 'array',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'add_user_to_blog',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'User added to blog', 'advanced-analytics' ),
					'description'     => __( 'Fires when a user is added to a multisite blog.', 'advanced-analytics' ),
					'category'        => 'user',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'user_id',
								'type'            => 'user_id',
								'extraction_code' => '',
							),
							array(
								'name'            => 'role',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'blog_id',
								'type'            => 'blog_id',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'remove_user_from_blog',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'User removed from blog', 'advanced-analytics' ),
					'description'     => __( 'Fires when a user is removed from a multisite blog.', 'advanced-analytics' ),
					'category'        => 'user',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'user_id',
								'type'            => 'user_id',
								'extraction_code' => '',
							),
							array(
								'name'            => 'blog_id',
								'type'            => 'blog_id',
								'extraction_code' => '',
							),
						)
					),
				),

				// Comment hooks.
				array(
					'hook_name'       => 'comment_post',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Comment posted', 'advanced-analytics' ),
					'description'     => __( 'Fires after a comment is submitted.', 'advanced-analytics' ),
					'category'        => 'core',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'comment_id',
								'type'            => 'comment_id',
								'extraction_code' => '',
							),
							array(
								'name'            => 'status',
								'type'            => 'string',
								'extraction_code' => '',
							),
						)
					),
				),

				// File and media (upload/delete) hooks (moved out of comment_post parameters).
				array(
					'hook_name'       => 'wp_handle_upload_prefilter',
					'hook_type'       => 'filter',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Upload prefilter', 'advanced-analytics' ),
					'description'     => __( 'Filter before a file is uploaded (pre-filter).', 'advanced-analytics' ),
					'category'        => 'core',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'file',
								'type'            => 'array',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'wp_handle_upload',
					'hook_type'       => 'filter',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Upload handled', 'advanced-analytics' ),
					'description'     => __( 'Filter after WordPress handles an upload (upload result array).', 'advanced-analytics' ),
					'category'        => 'core',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'upload',
								'type'            => 'array',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'add_attachment',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Attachment added', 'advanced-analytics' ),
					'description'     => __( 'Fires when a new attachment (uploaded file) is added.', 'advanced-analytics' ),
					'category'        => 'core',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'post_id',
								'type'            => 'post_id',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'edit_attachment',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Attachment edited', 'advanced-analytics' ),
					'description'     => __( 'Fires when an attachment is edited.', 'advanced-analytics' ),
					'category'        => 'core',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'post_id',
								'type'            => 'post_id',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'delete_attachment',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Attachment deleted', 'advanced-analytics' ),
					'description'     => __( 'Fires when an attachment is deleted.', 'advanced-analytics' ),
					'category'        => 'core',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'post_id',
								'type'            => 'post_id',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'wp_generate_attachment_metadata',
					'hook_type'       => 'filter',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Attachment metadata generated', 'advanced-analytics' ),
					'description'     => __( 'Filter/action when attachment metadata is generated (sizes, etc.).', 'advanced-analytics' ),
					'category'        => 'core',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'metadata',
								'type'            => 'array',
								'extraction_code' => '',
							),
							array(
								'name'            => 'attachment_id',
								'type'            => 'post_id',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'wp_update_attachment_metadata',
					'hook_type'       => 'filter',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Attachment metadata updated', 'advanced-analytics' ),
					'description'     => __( 'Filter when attachment metadata is updated.', 'advanced-analytics' ),
					'category'        => 'core',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'meta',
								'type'            => 'array',
								'extraction_code' => '',
							),
							array(
								'name'            => 'post_id',
								'type'            => 'post_id',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'wp_delete_file',
					'hook_type'       => 'filter',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'File deleted', 'advanced-analytics' ),
					'description'     => __( 'Filter applied when a file is deleted from disk.', 'advanced-analytics' ),
					'category'        => 'core',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'file',
								'type'            => 'string',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'edit_comment',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Comment edited', 'advanced-analytics' ),
					'description'     => __( 'Fires when a comment is edited.', 'advanced-analytics' ),
					'category'        => 'core',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'comment_id',
								'type'            => 'comment_id',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'save_post',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Post saved', 'advanced-analytics' ),
					'description'     => __( 'Fires when a post is saved', 'advanced-analytics' ),
					'category'        => 'post',
					'hook_parameters' => '',
				),
				array(
					'hook_name'       => 'delete_post',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Post deleted', 'advanced-analytics' ),
					'description'     => __( 'Fires when a post is deleted', 'advanced-analytics' ),
					'category'        => 'post',
					'hook_parameters' => '',
				),
				array(
					'hook_name'       => 'transition_post_status',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Post status transitioned', 'advanced-analytics' ),
					'description'     => __( 'Fires when a post is transitioned from one status to another', 'advanced-analytics' ),
					'category'        => 'post',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'new_status',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'old_status',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'post',
								'type'            => 'wp_post',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'wp_trash_post',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Post trashed', 'advanced-analytics' ),
					'description'     => __( 'Fires before a post is sent to the Trash', 'advanced-analytics' ),
					'category'        => 'post',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'post_id',
								'type'            => 'post_id',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'trashed_post',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Post trashed (after)', 'advanced-analytics' ),
					'description'     => __( 'Fires after a post is sent to the Trash', 'advanced-analytics' ),
					'category'        => 'post',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'post_id',
								'type'            => 'post_id',
								'extraction_code' => '',
							),
							array(
								'name'            => 'previous_status',
								'type'            => 'string',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'untrash_post',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Post untrashed', 'advanced-analytics' ),
					'description'     => __( 'Fires before a post is restored from the Trash', 'advanced-analytics' ),
					'category'        => 'post',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'post_id',
								'type'            => 'post_id',
								'extraction_code' => '',
							),
							array(
								'name'            => 'previous_status',
								'type'            => 'string',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'untrashed_post',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Post untrashed (after)', 'advanced-analytics' ),
					'description'     => __( 'Fires after a post is restored from the Trash', 'advanced-analytics' ),
					'category'        => 'post',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'post_id',
								'type'            => 'post_id',
								'extraction_code' => '',
							),
							array(
								'name'            => 'previous_status',
								'type'            => 'string',
								'extraction_code' => '',
							),
						)
					),
				),

				// Update hooks.
				array(
					'hook_name'       => 'upgrader_process_complete',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Upgrader process complete', 'advanced-analytics' ),
					'description'     => __( 'Fires when the upgrader process is complete (core, plugin, theme updates)', 'advanced-analytics' ),
					'category'        => 'update',
					'hook_parameters' => '',
				),
				array(
					'hook_name'       => '_core_updated_successfully',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Core updated successfully', 'advanced-analytics' ),
					'description'     => __( 'Fires when WordPress core is successfully updated', 'advanced-analytics' ),
					'category'        => 'update',
					'hook_parameters' => '',
				),
				array(
					'hook_name'       => 'activated_plugin',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Plugin activated', 'advanced-analytics' ),
					'description'     => __( 'Fires after a plugin has been activated', 'advanced-analytics' ),
					'category'        => 'update',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'plugin',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'network_wide',
								'type'            => 'bool',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'deactivated_plugin',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Plugin deactivated', 'advanced-analytics' ),
					'description'     => __( 'Fires after a plugin has been deactivated', 'advanced-analytics' ),
					'category'        => 'update',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'plugin',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'network_deactivating',
								'type'            => 'bool',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'deleted_plugin',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Plugin deleted', 'advanced-analytics' ),
					'description'     => __( 'Fires immediately before a plugin deletion attempt', 'advanced-analytics' ),
					'category'        => 'update',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'plugin_file',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'deleted',
								'type'            => 'bool',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'switch_theme',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Theme switched', 'advanced-analytics' ),
					'description'     => __( 'Fires after the theme is switched', 'advanced-analytics' ),
					'category'        => 'update',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'new_name',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'new_theme',
								'type'            => 'object',
								'extraction_code' => '',
							),
							array(
								'name'            => 'old_theme',
								'type'            => 'object',
								'extraction_code' => '',
							),
						)
					),
				),

				// Automatic update hooks.
				array(
					'hook_name'       => 'pre_auto_update',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 3,
					'capture_output'  => 0,
					'hook_label'      => __( 'Pre auto update', 'advanced-analytics' ),
					'description'     => __( 'Fires before an automatic update begins', 'advanced-analytics' ),
					'category'        => 'update',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'type',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'item',
								'type'            => 'mixed',
								'extraction_code' => '',
							),
							array(
								'name'            => 'context',
								'type'            => 'mixed',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'automatic_updates_complete',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Automatic updates complete', 'advanced-analytics' ),
					'description'     => __( 'Fires when automatic updates are complete', 'advanced-analytics' ),
					'category'        => 'update',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'update_results',
								'type'            => 'array',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'automatic_updater_disabled',
					'hook_type'       => 'filter',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Automatic updater disabled', 'advanced-analytics' ),
					'description'     => __( 'Filters whether to entirely disable background updates', 'advanced-analytics' ),
					'category'        => 'update',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'disabled',
								'type'            => 'bool',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'send_core_update_notification_email',
					'hook_type'       => 'filter',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 2,
					'capture_output'  => 0,
					'hook_label'      => __( 'Send core update notification email', 'advanced-analytics' ),
					'description'     => __( 'Filters whether to send core update notification emails', 'advanced-analytics' ),
					'category'        => 'update',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'notify',
								'type'            => 'bool',
								'extraction_code' => '',
							),
							array(
								'name'            => 'item',
								'type'            => 'mixed',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'auto_core_update_send_email',
					'hook_type'       => 'filter',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 4,
					'capture_output'  => 0,
					'hook_label'      => __( 'Auto core update send email', 'advanced-analytics' ),
					'description'     => __( 'Filters whether to send core update emails', 'advanced-analytics' ),
					'category'        => 'update',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'send_email',
								'type'            => 'bool',
								'extraction_code' => '',
							),
							array(
								'name'            => 'type',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'core_update',
								'type'            => 'object',
								'extraction_code' => '',
							),
							array(
								'name'            => 'result',
								'type'            => 'mixed',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'auto_core_update_email',
					'hook_type'       => 'filter',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 4,
					'capture_output'  => 0,
					'hook_label'      => __( 'Auto core update email', 'advanced-analytics' ),
					'description'     => __( 'Filters the core update email content', 'advanced-analytics' ),
					'category'        => 'update',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'email',
								'type'            => 'array',
								'extraction_code' => '',
							),
							array(
								'name'            => 'type',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'core_update',
								'type'            => 'object',
								'extraction_code' => '',
							),
							array(
								'name'            => 'result',
								'type'            => 'mixed',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'auto_plugin_update_send_email',
					'hook_type'       => 'filter',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 2,
					'capture_output'  => 0,
					'hook_label'      => __( 'Auto plugin update send email', 'advanced-analytics' ),
					'description'     => __( 'Filters whether to send plugin update emails', 'advanced-analytics' ),
					'category'        => 'update',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'send_email',
								'type'            => 'bool',
								'extraction_code' => '',
							),
							array(
								'name'            => 'plugin_updates',
								'type'            => 'array',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'auto_theme_update_send_email',
					'hook_type'       => 'filter',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 2,
					'capture_output'  => 0,
					'hook_label'      => __( 'Auto theme update send email', 'advanced-analytics' ),
					'description'     => __( 'Filters whether to send theme update emails', 'advanced-analytics' ),
					'category'        => 'update',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'send_email',
								'type'            => 'bool',
								'extraction_code' => '',
							),
							array(
								'name'            => 'theme_updates',
								'type'            => 'array',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'auto_plugin_theme_update_email',
					'hook_type'       => 'filter',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 4,
					'capture_output'  => 0,
					'hook_label'      => __( 'Auto plugin theme update email', 'advanced-analytics' ),
					'description'     => __( 'Filters plugin and theme update email content', 'advanced-analytics' ),
					'category'        => 'update',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'email',
								'type'            => 'array',
								'extraction_code' => '',
							),
							array(
								'name'            => 'type',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'successful_updates',
								'type'            => 'array',
								'extraction_code' => '',
							),
							array(
								'name'            => 'failed_updates',
								'type'            => 'array',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'automatic_updates_send_debug_email',
					'hook_type'       => 'filter',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Automatic updates send debug email', 'advanced-analytics' ),
					'description'     => __( 'Filters whether to send automatic updates debug emails', 'advanced-analytics' ),
					'category'        => 'update',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'development_version',
								'type'            => 'bool',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'automatic_updates_debug_email',
					'hook_type'       => 'filter',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 3,
					'capture_output'  => 0,
					'hook_label'      => __( 'Automatic updates debug email', 'advanced-analytics' ),
					'description'     => __( 'Filters the automatic updates debug email content', 'advanced-analytics' ),
					'category'        => 'update',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'email',
								'type'            => 'array',
								'extraction_code' => '',
							),
							array(
								'name'            => 'failures',
								'type'            => 'array',
								'extraction_code' => '',
							),
							array(
								'name'            => 'update_results',
								'type'            => 'array',
								'extraction_code' => '',
							),
						)
					),
				),

				// Core hooks.
				array(
					'hook_name'       => 'init',
					'hook_type'       => 'action',
					'priority'        => 99999, // Very late to capture most initialization.
					'enabled'         => 0,
					'capture_args'    => 0,
					'capture_output'  => 0,
					'hook_label'      => __( 'WordPress initialized', 'advanced-analytics' ),
					'description'     => __( 'Fires after WordPress has finished loading but before headers are sent', 'advanced-analytics' ),
					'category'        => 'core',
					'hook_parameters' => '',
				),
				array(
					'hook_name'       => 'wp',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 0,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'WordPress loaded', 'advanced-analytics' ),
					'description'     => __( 'Fires once the WordPress environment has been set up', 'advanced-analytics' ),
					'category'        => 'core',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'wp',
								'type'            => 'object',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'shutdown',
					'hook_type'       => 'action',
					'priority'        => 1, // Early to capture shutdown sequence.
					'enabled'         => 0,
					'capture_args'    => 0,
					'capture_output'  => 0,
					'hook_label'      => __( 'WordPress shutdown', 'advanced-analytics' ),
					'description'     => __( 'Fires at the end of WordPress execution', 'advanced-analytics' ),
					'category'        => 'core',
					'hook_parameters' => '',
				),

				// WP-CLI hooks.
				array(
					'hook_name'       => 'cli_init',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 0,
					'capture_args'    => 0,
					'capture_output'  => 0,
					'hook_label'      => __( 'WP-CLI initialized', 'advanced-analytics' ),
					'description'     => __( 'Fires when WP-CLI is initialized', 'advanced-analytics' ),
					'category'        => 'core',
					'hook_parameters' => '',
				),
				// Settings and options hooks.
				array(
					'hook_name'       => 'update_option',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 0,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Option updated', 'advanced-analytics' ),
					'description'     => __( 'Generic option update action. Captures any option change (option name, old value, new value).', 'advanced-analytics' ),
					'category'        => 'core',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'option',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'old_value',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'new_value',
								'type'            => 'string',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'update_site_option',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 0,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Site option updated', 'advanced-analytics' ),
					'description'     => __( 'Site option update (multisite). Captures option name, old value, new value, site id.', 'advanced-analytics' ),
					'category'        => 'core',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'option',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'old_value',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'new_value',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'blog_id',
								'type'            => 'blog_id',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'update_network_option',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 0,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Network option updated', 'advanced-analytics' ),
					'description'     => __( 'Network option update (multisite). Captures option name, old value, new value, network id.', 'advanced-analytics' ),
					'category'        => 'core',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'option',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'old_value',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'new_value',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'network_id',
								'type'            => 'int',
								'extraction_code' => '',
							),
						)
					),
				),
				// Targeted option changes (site identity, permalinks, timezone, formatting, reading/discussion settings etc.).
				array(
					'hook_name'       => 'update_option_blogname',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Site title changed', 'advanced-analytics' ),
					'description'     => __( 'Site title (blogname) changed.', 'advanced-analytics' ),
					'category'        => 'core',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'old_value',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'new_value',
								'type'            => 'string',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'update_option_blogdescription',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Site tagline changed', 'advanced-analytics' ),
					'description'     => __( 'Site tagline (blogdescription) changed.', 'advanced-analytics' ),
					'category'        => 'core',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'old_value',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'new_value',
								'type'            => 'string',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'update_option_home',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Home URL changed', 'advanced-analytics' ),
					'description'     => __( 'Site Home URL changed.', 'advanced-analytics' ),
					'category'        => 'core',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'old_value',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'new_value',
								'type'            => 'string',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'update_option_siteurl',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Site URL changed', 'advanced-analytics' ),
					'description'     => __( 'Site URL (siteurl) changed.', 'advanced-analytics' ),
					'category'        => 'core',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'old_value',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'new_value',
								'type'            => 'string',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'update_option_permalink_structure',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Permalink structure changed', 'advanced-analytics' ),
					'description'     => __( 'Permalink structure changed.', 'advanced-analytics' ),
					'category'        => 'core',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'old_value',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'new_value',
								'type'            => 'string',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'update_option_category_base',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Category base changed', 'advanced-analytics' ),
					'description'     => __( 'Category base changed.', 'advanced-analytics' ),
					'category'        => 'core',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'old_value',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'new_value',
								'type'            => 'string',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'update_option_tag_base',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Tag base changed', 'advanced-analytics' ),
					'description'     => __( 'Tag base changed.', 'advanced-analytics' ),
					'category'        => 'core',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'old_value',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'new_value',
								'type'            => 'string',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'update_option_timezone_string',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Timezone changed', 'advanced-analytics' ),
					'description'     => __( 'Timezone string changed.', 'advanced-analytics' ),
					'category'        => 'core',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'old_value',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'new_value',
								'type'            => 'string',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'update_option_date_format',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Date format changed', 'advanced-analytics' ),
					'description'     => __( 'Date format changed.', 'advanced-analytics' ),
					'category'        => 'core',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'old_value',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'new_value',
								'type'            => 'string',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'update_option_time_format',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Time format changed', 'advanced-analytics' ),
					'description'     => __( 'Time format changed.', 'advanced-analytics' ),
					'category'        => 'core',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'old_value',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'new_value',
								'type'            => 'string',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'update_option_start_of_week',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Start of week changed', 'advanced-analytics' ),
					'description'     => __( 'Start of week setting changed.', 'advanced-analytics' ),
					'category'        => 'core',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'old_value',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'new_value',
								'type'            => 'string',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'update_option_posts_per_page',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Posts per page changed', 'advanced-analytics' ),
					'description'     => __( 'Posts per page setting changed.', 'advanced-analytics' ),
					'category'        => 'core',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'old_value',
								'type'            => 'int',
								'extraction_code' => '',
							),
							array(
								'name'            => 'new_value',
								'type'            => 'int',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'update_option_users_can_register',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Membership setting changed', 'advanced-analytics' ),
					'description'     => __( 'Membership setting (users_can_register) changed.', 'advanced-analytics' ),
					'category'        => 'core',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'old_value',
								'type'            => 'int',
								'extraction_code' => '',
							),
							array(
								'name'            => 'new_value',
								'type'            => 'int',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'update_option_default_comment_status',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Default comment status changed', 'advanced-analytics' ),
					'description'     => __( 'Default comment status changed (open/closed).', 'advanced-analytics' ),
					'category'        => 'core',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'old_value',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'new_value',
								'type'            => 'string',
								'extraction_code' => '',
							),
						)
					),
				),

				// Email hooks.
				array(
					'hook_name'       => 'wp_mail_failed',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Email failed', 'advanced-analytics' ),
					'description'     => __( 'Fires when an email fails to send.', 'advanced-analytics' ),
					'category'        => 'email',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'wp_error',
								'type'            => 'object',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'wp_mail_succeeded',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Email sent', 'advanced-analytics' ),
					'description'     => __( 'Fires when an email is successfully sent.', 'advanced-analytics' ),
					'category'        => 'email',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'mail_data',
								'type'            => 'array',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'phpmailer_init',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'PHPMailer initialized', 'advanced-analytics' ),
					'description'     => __( 'Fires before sending an email, allowing modification of PHPMailer object.', 'advanced-analytics' ),
					'category'        => 'email',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'phpmailer',
								'type'            => 'object',
								'extraction_code' => '',
							),
						)
					),
				),

				// Additional post hooks.
				array(
					'hook_name'       => 'publish_post',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 2,
					'capture_output'  => 0,
					'hook_label'      => __( 'Post published', 'advanced-analytics' ),
					'description'     => __( 'Fires when a post is published.', 'advanced-analytics' ),
					'category'        => 'post',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'post_id',
								'type'            => 'post_id',
								'extraction_code' => '',
							),
							array(
								'name'            => 'post',
								'type'            => 'wp_post',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'wp_insert_post',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 3,
					'capture_output'  => 0,
					'hook_label'      => __( 'Post inserted', 'advanced-analytics' ),
					'description'     => __( 'Fires when a post is inserted into the database.', 'advanced-analytics' ),
					'category'        => 'post',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'post_id',
								'type'            => 'post_id',
								'extraction_code' => '',
							),
							array(
								'name'            => 'post',
								'type'            => 'wp_post',
								'extraction_code' => '',
							),
							array(
								'name'            => 'update',
								'type'            => 'bool',
								'extraction_code' => '',
							),
						)
					),
				),

				// Additional comment hooks.
				array(
					'hook_name'       => 'wp_set_comment_status',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 2,
					'capture_output'  => 0,
					'hook_label'      => __( 'Comment status changed', 'advanced-analytics' ),
					'description'     => __( 'Fires when a comment status is changed.', 'advanced-analytics' ),
					'category'        => 'comment',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'comment_id',
								'type'            => 'comment_id',
								'extraction_code' => '',
							),
							array(
								'name'            => 'comment_status',
								'type'            => 'string',
								'extraction_code' => '',
							),
						)
					),
				),

				// Additional user hooks.
				array(
					'hook_name'       => 'wp_authenticate',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 0,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'User authentication attempted', 'advanced-analytics' ),
					'description'     => __( 'Fires before user authentication.', 'advanced-analytics' ),
					'category'        => 'auth',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'username',
								'type'            => 'string',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'retrieve_password',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Password reset requested', 'advanced-analytics' ),
					'description'     => __( 'Fires when a password reset is requested.', 'advanced-analytics' ),
					'category'        => 'auth',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'user_login',
								'type'            => 'string',
								'extraction_code' => '',
							),
						)
					),
				),

				// Admin hooks.
				array(
					'hook_name'       => 'admin_init',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 0,
					'capture_args'    => 0,
					'capture_output'  => 0,
					'hook_label'      => __( 'Admin initialized', 'advanced-analytics' ),
					'description'     => __( 'Fires as an admin screen or script is being initialized.', 'advanced-analytics' ),
					'category'        => 'admin',
					'hook_parameters' => '',
				),
				array(
					'hook_name'       => 'admin_menu',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 0,
					'capture_args'    => 0,
					'capture_output'  => 0,
					'hook_label'      => __( 'Admin menu built', 'advanced-analytics' ),
					'description'     => __( 'Fires before the administration menu loads in the admin.', 'advanced-analytics' ),
					'category'        => 'admin',
					'hook_parameters' => '',
				),

				// Additional update hooks.
				array(
					'hook_name'       => 'upgrader_pre_install',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 2,
					'capture_output'  => 0,
					'hook_label'      => __( 'Upgrader pre-install', 'advanced-analytics' ),
					'description'     => __( 'Fires before an upgrade starts.', 'advanced-analytics' ),
					'category'        => 'update',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'response',
								'type'            => 'bool',
								'extraction_code' => '',
							),
							array(
								'name'            => 'hook_extra',
								'type'            => 'array',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'upgrader_post_install',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 3,
					'capture_output'  => 0,
					'hook_label'      => __( 'Upgrader post-install', 'advanced-analytics' ),
					'description'     => __( 'Fires after an upgrade completes.', 'advanced-analytics' ),
					'category'        => 'update',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'response',
								'type'            => 'bool',
								'extraction_code' => '',
							),
							array(
								'name'            => 'hook_extra',
								'type'            => 'array',
								'extraction_code' => '',
							),
							array(
								'name'            => 'result',
								'type'            => 'bool',
								'extraction_code' => '',
							),
						)
					),
				),

				// Additional core hooks.
				array(
					'hook_name'       => 'wp_loaded',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 0,
					'capture_args'    => 0,
					'capture_output'  => 0,
					'hook_label'      => __( 'WordPress loaded', 'advanced-analytics' ),
					'description'     => __( 'Fires once WordPress is fully loaded.', 'advanced-analytics' ),
					'category'        => 'core',
					'hook_parameters' => '',
				),
				array(
					'hook_name'       => 'template_redirect',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 0,
					'capture_args'    => 0,
					'capture_output'  => 0,
					'hook_label'      => __( 'Template redirect', 'advanced-analytics' ),
					'description'     => __( 'Fires before determining which template to load.', 'advanced-analytics' ),
					'category'        => 'core',
					'hook_parameters' => '',
				),

				// Additional settings hooks.
				array(
					'hook_name'       => 'update_option_admin_email',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Admin email changed', 'advanced-analytics' ),
					'description'     => __( 'Admin email (admin_email) changed.', 'advanced-analytics' ),
					'category'        => 'core',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'old_value',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'new_value',
								'type'            => 'string',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'update_option_blog_charset',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 1,
					'capture_args'    => 1,
					'capture_output'  => 0,
					'hook_label'      => __( 'Blog charset changed', 'advanced-analytics' ),
					'description'     => __( 'Blog charset changed.', 'advanced-analytics' ),
					'category'        => 'core',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'old_value',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'new_value',
								'type'            => 'string',
								'extraction_code' => '',
							),
						)
					),
				),
				// wpdb-generated hooks (SQL queries).
				array(
					'hook_name'       => 'query',
					'hook_type'       => 'filter',
					'priority'        => 10,
					'enabled'         => 0,
					'capture_args'    => 1,
					'capture_output'  => 1,
					'hook_label'      => __( 'Database Query', 'advanced-analytics' ),
					'description'     => __( 'Filters the database query before execution', 'advanced-analytics' ),
					'category'        => 'core',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'query',
								'type'            => 'string',
								'extraction_code' => '',
							),
						)
					),
				),
				array(
					'hook_name'       => 'wp_db_error',
					'hook_type'       => 'action',
					'priority'        => 10,
					'enabled'         => 0,
					'capture_args'    => 3,
					'capture_output'  => 0,
					'hook_label'      => __( 'Database Error', 'advanced-analytics' ),
					'description'     => __( 'Fires when a database error occurs', 'advanced-analytics' ),
					'category'        => 'core',
					'hook_parameters' => wp_json_encode(
						array(
							array(
								'name'            => 'error',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'query',
								'type'            => 'string',
								'extraction_code' => '',
							),
							array(
								'name'            => 'last_query',
								'type'            => 'string',
								'extraction_code' => '',
							),
						)
					),
				),
			);

			$current_time = microtime( true );

			foreach ( $default_hooks as $hook ) {
				// Check if hook already exists.
				$exists = self::hook_exists( $hook['hook_name'], $hook['hook_type'] );

				if ( ! $exists ) {
					$hook['date_added']    = $current_time;
					$hook['date_modified'] = $current_time;

					self::insert( $hook );
				}
			}
		}

		/**
		 * Check if a hook already exists.
		 *
		 * @param string $hook_name Hook name.
		 * @param string $hook_type Hook type.
		 *
		 * @return bool
		 *
		 * @since 4.5.0
		 */
		private static function hook_exists( string $hook_name, string $hook_type ): bool {
			return self::count( 'hook_name = %s AND hook_type = %s', array( $hook_name, $hook_type ) ) > 0;
		}

		/**
		 * Returns the table CMS admin fields
		 *
		 * @return array
		 *
		 * @since 4.5.0
		 */
		public static function get_column_names_admin(): array {
			return array(
				'hook_name'      => __( 'Hook Name', '0-day-analytics' ),
				'hook_label'     => __( 'Display Name', '0-day-analytics' ),
				'hook_type'      => __( 'Type', '0-day-analytics' ),
				'priority'       => __( 'Priority', '0-day-analytics' ),
				'enabled'        => __( 'Enabled', '0-day-analytics' ),
				'capture_args'   => __( 'Capture Args', '0-day-analytics' ),
				'capture_output' => __( 'Capture Output', '0-day-analytics' ),
				'category'       => __( 'Category', '0-day-analytics' ),
				'group_id'       => __( 'Group', '0-day-analytics' ),
				'description'    => __( 'Description', '0-day-analytics' ),
			);
		}

		/**
		 * Get all enabled hooks for capturing.
		 *
		 * @return array
		 *
		 * @since 4.5.0
		 */
		public static function get_enabled_hooks(): array {
			$cache_key = 'advan_enabled_hooks';
			$cached    = \wp_cache_get( $cache_key, 'advan' );

			if ( false !== $cached && is_array( $cached ) ) {
				return $cached;
			}

			$sql     = 'SELECT * FROM ' . self::get_table_name() . ' WHERE enabled = 1 ORDER BY priority ASC';
			$results = self::get_results( $sql );

			if ( ! is_array( $results ) ) {
				$results = array();
			}

			\wp_cache_set( $cache_key, $results, 'advan', 300 ); // Cache for 5 minutes.

			return $results;
		}

		/**
		 * Clear cached hook labels map.
		 *
		 * @return void
		 */
		public static function clear_hook_labels_cache(): void {
			\wp_cache_delete( 'advan_hook_labels', 'advan' );
		}

		/**
		 * Returns a map of hook_name => hook_label for quick lookups.
		 *
		 * @return array
		 */
		public static function get_hook_labels_map(): array {
			$cache_key = 'advan_hook_labels';
			$cached    = \wp_cache_get( $cache_key, 'advan' );

			if ( is_array( $cached ) ) {
				return $cached;
			}

			$labels = array();
			$table  = self::get_table_name();

			$results = self::get_results( 'SELECT hook_name, hook_label FROM ' . $table );

			if ( is_array( $results ) ) {
				foreach ( $results as $row ) {
					if ( empty( $row['hook_name'] ) ) {
						continue;
					}
					$labels[ $row['hook_name'] ] = isset( $row['hook_label'] ) ? (string) $row['hook_label'] : '';
				}
			}

			\wp_cache_set( $cache_key, $labels, 'advan', 300 );

			return $labels;
		}

		/**
		 * Get human readable label for a hook name.
		 *
		 * @param string $hook_name Hook name.
		 *
		 * @return string
		 */
		public static function get_hook_label( string $hook_name ): string {
			$labels = self::get_hook_labels_map();
			return isset( $labels[ $hook_name ] ) ? (string) $labels[ $hook_name ] : '';
		}

		/**
		 * Toggle hook enabled status.
		 *
		 * @param int $id Hook ID.
		 *
		 * @return bool
		 *
		 * @since 4.5.0
		 */
		public static function toggle_enabled( int $id ): bool {
			if ( $id <= 0 ) {
				return false;
			}

			// Load current record.
			$current = self::load( 'id = %d', $id );
			if ( ! $current ) {
				return false;
			}

			// Toggle enabled status and update date_modified.
			$current['enabled']       = $current['enabled'] ? 0 : 1;
			$current['date_modified'] = microtime( true );

			$result = self::insert( $current );

			// Clear caches.
			\wp_cache_delete( 'advan_enabled_hooks', 'advan' );
			self::clear_hook_labels_cache();

			return $result > 0;
		}

		/**
		 * Update hook data.
		 *
		 * @param int   $id   Hook ID.
		 * @param array $data Data to update.
		 *
		 * @return bool
		 *
		 * @since 4.5.0
		 */
		public static function update( int $id, array $data ): bool {
			if ( $id <= 0 || empty( $data ) ) {
				return false;
			}

			// Load current record.
			$current = self::load( 'id = %d', $id );
			if ( ! $current ) {
				return false;
			}

			// Merge new data with existing data.
			$current = array_merge( $current, $data );

			$result = self::insert( $current );

			// Clear cache.
			\wp_cache_delete( 'advan_enabled_hooks', 'advan' );
			self::clear_hook_labels_cache();

			return $result > 0;
		}

		/**
		 * Set enabled status for a hook.
		 *
		 * @param int  $id      Hook ID.
		 * @param bool $enabled Enabled status.
		 *
		 * @return bool
		 *
		 * @since 4.7.0
		 */
		public static function set_enabled( int $id, bool $enabled ): bool {
			return self::update(
				$id,
				array(
					'enabled'       => $enabled ? 1 : 0,
					'date_modified' => microtime( true ),
				)
			);
		}

		/**
		 * Set group ID for a hook.
		 *
		 * @param int $id       Hook ID.
		 * @param int $group_id Group ID.
		 *
		 * @return bool
		 *
		 * @since 4.7.0
		 */
		public static function set_group_id( int $id, int $group_id ): bool {
			return self::update(
				$id,
				array(
					'group_id'      => $group_id,
					'date_modified' => microtime( true ),
				)
			);
		}

		/**
		 * Get hook parameters by hook name.
		 *
		 * @param string $hook_name Hook name.
		 *
		 * @return string
		 *
		 * @since 4.7.0
		 */
		public static function get_hook_parameters( string $hook_name ): string {
			$hook = self::load( 'hook_name = %s', array( $hook_name ) );
			return $hook && isset( $hook['hook_parameters'] ) ? $hook['hook_parameters'] : '';
		}
	}
}
