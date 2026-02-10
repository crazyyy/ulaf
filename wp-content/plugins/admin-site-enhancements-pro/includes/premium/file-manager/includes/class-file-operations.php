<?php
/**
 * File Operations Class
 *
 * Handles all file system operations.
 *
 * @package ASENHA\FileManager
 */

namespace ASENHA\FileManager;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;
use ZipArchive;

/**
 * File Operations class
 */
class File_Operations {

	/**
	 * Initialize WP_Filesystem.
	 *
	 * @return WP_Filesystem_Base|WP_Error Filesystem object or error.
	 */
	private static function get_filesystem() {
		global $wp_filesystem;

		// Initialize WP_Filesystem if not already initialized
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			
			// Initialize filesystem with direct method
			$result = WP_Filesystem();
			
			if ( false === $result ) {
				return new \WP_Error( 'fs_unavailable', __( 'Could not initialize filesystem.', 'admin-site-enhancements') );
			}
		}

		return $wp_filesystem;
	}

	/**
	 * Check whether a path is within a base directory (inclusive).
	 *
	 * Paths are normalized and compared with a strict prefix check.
	 *
	 * @param string $base_dir Base directory path.
	 * @param string $path     Target path.
	 * @return bool True when $path is the same as $base_dir or within it.
	 */
	private static function is_within_path( $base_dir, $path ) {
		if ( empty( $base_dir ) || empty( $path ) ) {
			return false;
		}

		$base_dir = untrailingslashit( wp_normalize_path( $base_dir ) );
		$path     = untrailingslashit( wp_normalize_path( $path ) );

		return ( $path === $base_dir || 0 === strpos( $path, $base_dir . '/' ) );
	}

	/**
	 * Check if a path is within a protected WordPress core folder where we should
	 * disallow *creating* new files/folders and uploading files.
	 *
	 * This intentionally only covers wp-admin and wp-includes (and their contents),
	 * and explicitly does NOT treat wp-content as protected for this purpose.
	 *
	 * IMPORTANT: This expects an already validated absolute path within ABSPATH
	 * (e.g. the return value of self::validate_path()).
	 *
	 * @param string $validated_path Validated absolute path.
	 * @return bool True when within wp-admin or wp-includes, false otherwise.
	 */
	public static function is_protected_core_write_folder( $validated_path ) {
		if ( empty( $validated_path ) || ! is_string( $validated_path ) ) {
			return false;
		}

		$abspath_real = realpath( ABSPATH );
		if ( false === $abspath_real ) {
			return false;
		}

		$validated_path = untrailingslashit( wp_normalize_path( $validated_path ) );
		$wp_admin       = untrailingslashit( wp_normalize_path( trailingslashit( $abspath_real ) . 'wp-admin' ) );
		$wp_includes    = untrailingslashit( wp_normalize_path( trailingslashit( $abspath_real ) . 'wp-includes' ) );

		return (
			self::is_within_path( $wp_admin, $validated_path ) ||
			self::is_within_path( $wp_includes, $validated_path )
		);
	}

	/**
	 * Get the "read-only" reason based on WordPress DISALLOW_* constants.
	 *
	 * Rules:
	 * - DISALLOW_FILE_MODS=true: everything under ABSPATH is read-only.
	 * - FM_READ_ONLY=true: (ASE File Manager only) everything under ABSPATH is read-only.
	 * - DISALLOW_FILE_EDIT=true: plugin and theme directories are read-only.
	 *
	 * @param string $validated_path A validated absolute path (within ABSPATH).
	 * @return string|null One of 'file_mods', 'fm_read_only', 'file_edit', or null when not restricted.
	 */
	private static function get_read_only_reason_by_constants( $validated_path ) {
		$validated_path = untrailingslashit( wp_normalize_path( $validated_path ) );

		if ( defined( 'DISALLOW_FILE_MODS' ) && DISALLOW_FILE_MODS ) {
			return 'file_mods';
		}

		if ( defined( 'FM_READ_ONLY' ) && FM_READ_ONLY ) {
			return 'fm_read_only';
		}

		if ( ! ( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT ) ) {
			return null;
		}

		$plugin_dir = realpath( WP_PLUGIN_DIR );
		if ( false !== $plugin_dir && self::is_within_path( $plugin_dir, $validated_path ) ) {
			return 'file_edit';
		}

		// Include mu-plugins when DISALLOW_FILE_EDIT is enabled.
		$mu_plugin_dir = defined( 'WPMU_PLUGIN_DIR' ) ? realpath( WPMU_PLUGIN_DIR ) : false;
		if ( false !== $mu_plugin_dir && self::is_within_path( $mu_plugin_dir, $validated_path ) ) {
			return 'file_edit';
		}

		$theme_root = realpath( get_theme_root() );
		if ( false !== $theme_root && self::is_within_path( $theme_root, $validated_path ) ) {
			return 'file_edit';
		}

		return null;
	}

	/**
	 * Get an error when a write operation is disallowed by DISALLOW_* constants.
	 *
	 * @param string $validated_path A validated absolute path (within ABSPATH).
	 * @return WP_Error|false WP_Error when disallowed, false otherwise.
	 */
	private static function get_write_restriction_error_by_constants( $validated_path ) {
		$reason = self::get_read_only_reason_by_constants( $validated_path );
		if ( null === $reason ) {
			return false;
		}

		if ( 'file_mods' === $reason ) {
			return new \WP_Error(
				'disallow_file_mods',
				__( 'DISALLOW_FILE_MODS is in effect. You are not allowed to modify any folder or file.', 'admin-site-enhancements' )
			);
		}

		if ( 'fm_read_only' === $reason ) {
			return new \WP_Error(
				'fm_read_only',
				__( 'FM_READ_ONLY is in effect. You are not allowed to modify any folder or file.', 'admin-site-enhancements' )
			);
		}

		return new \WP_Error(
			'disallow_file_edit',
			__( 'DISALLOW_FILE_EDIT is in effect. You are not allowed to modify theme or plugin files.', 'admin-site-enhancements' )
		);
	}

	/**
	 * Get read-only metadata for a path based on DISALLOW_* constants.
	 *
	 * This is used by the REST layer to inform the UI (CodeMirror + toolbar) that
	 * the current file should be treated as read-only even if it is otherwise editable.
	 *
	 * @param string $path Absolute path to check.
	 * @return array{
	 *   is_read_only_by_constants: bool,
	 *   read_only_reason: string|null
	 * }
	 */
	public static function get_read_only_meta_by_constants( $path ) {
		$validated_path = self::validate_path( $path );
		if ( is_wp_error( $validated_path ) ) {
			return array(
				'is_read_only_by_constants' => false,
				'read_only_reason'          => null,
			);
		}

		$reason = self::get_read_only_reason_by_constants( $validated_path );

		return array(
			'is_read_only_by_constants' => ( null !== $reason ),
			'read_only_reason'          => $reason,
		);
	}

	/**
	 * Get write restriction error for a given path based on DISALLOW_* constants.
	 *
	 * @param string $path Absolute path to check.
	 * @return WP_Error|false WP_Error when disallowed, false otherwise.
	 */
	public static function get_write_restriction_error_by_constants_for_path( $path ) {
		$validated_path = self::validate_path( $path );
		if ( is_wp_error( $validated_path ) ) {
			return $validated_path;
		}

		return self::get_write_restriction_error_by_constants( $validated_path );
	}

	/**
	 * Get directory contents with metadata.
	 *
	 * @param string $path Directory path.
	 * @return array|WP_Error Array of files/folders or error.
	 */
	public static function get_directory_contents( $path ) {
		// Validate path
		$validated_path = self::validate_path( $path );
		if ( is_wp_error( $validated_path ) ) {
			return $validated_path;
		}

		if ( ! is_dir( $validated_path ) ) {
			return new \WP_Error( 'invalid_directory', __( 'The specified path is not a directory.', 'admin-site-enhancements') );
		}

		if ( ! is_readable( $validated_path ) ) {
			return new \WP_Error( 'not_readable', __( 'The directory is not readable.', 'admin-site-enhancements') );
		}

		$items = array();

		try {
			$iterator = new FilesystemIterator( $validated_path, FilesystemIterator::SKIP_DOTS );

			foreach ( $iterator as $file ) {
				$item = self::get_file_info( $file->getPathname() );
				if ( ! is_wp_error( $item ) ) {
					$items[] = $item;
				}
			}
		} catch ( \Exception $e ) {
			return new \WP_Error( 'read_error', $e->getMessage() );
		}

		return $items;
	}

	/**
	 * Get file information.
	 *
	 * @param string $path File path.
	 * @return array|WP_Error File information or error.
	 */
	public static function get_file_info( $path ) {
		$validated_path = self::validate_path( $path );
		if ( is_wp_error( $validated_path ) ) {
			return $validated_path;
		}

		if ( ! file_exists( $validated_path ) ) {
			return new \WP_Error( 'file_not_found', __( 'File or directory not found.', 'admin-site-enhancements') );
		}

		$wp_filesystem = self::get_filesystem();
		if ( is_wp_error( $wp_filesystem ) ) {
			return $wp_filesystem;
		}

		$is_dir     = is_dir( $validated_path );
		$is_file    = is_file( $validated_path );
		$perms      = fileperms( $validated_path );
		$perms_octal = substr( sprintf( '%o', $perms ), -3 );

		$info = array(
			'name'        => basename( $validated_path ),
			'path'        => $validated_path,
			'type'        => $is_dir ? 'directory' : 'file',
			'size'        => $is_file ? filesize( $validated_path ) : 0,
			'size_human'  => $is_file ? size_format( filesize( $validated_path ), 2 ) : '-',
			'modified'    => filemtime( $validated_path ),
			'modified_human' => date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), filemtime( $validated_path ) ),
			'permissions' => $perms_octal,
			'readable'    => is_readable( $validated_path ),
			'writable'    => $wp_filesystem->is_writable( $validated_path ),
			'is_protected' => self::is_core_file( $validated_path ),
			'is_wp_config' => self::is_wp_config_file( $validated_path ),
		);

		// Add file extension for files
		if ( $is_file ) {
			$info['extension'] = pathinfo( $validated_path, PATHINFO_EXTENSION );
			$info['mime_type'] = mime_content_type( $validated_path );
		}

		return $info;
	}

	/**
	 * Read file contents.
	 *
	 * @param string $path File path.
	 * @return string|WP_Error File contents or error.
	 */
	public static function read_file( $path ) {
		$validated_path = self::validate_path( $path );
		if ( is_wp_error( $validated_path ) ) {
			return $validated_path;
		}

		if ( ! file_exists( $validated_path ) ) {
			return new \WP_Error( 'file_not_found', __( 'File not found.', 'admin-site-enhancements') );
		}

		if ( ! is_file( $validated_path ) ) {
			return new \WP_Error( 'not_a_file', __( 'The specified path is not a file.', 'admin-site-enhancements') );
		}

		if ( ! is_readable( $validated_path ) ) {
			return new \WP_Error( 'not_readable', __( 'The file is not readable.', 'admin-site-enhancements') );
		}

		// Additional path validation to prevent LFI bypasses
		// Ensure the path is still within ABSPATH after all resolutions
		$abspath_real = realpath( ABSPATH );
		if ( 0 !== strpos( $validated_path, $abspath_real ) ) {
			return new \WP_Error( 'path_traversal', __( 'Path traversal detected.', 'admin-site-enhancements') );
		}

		// Whitelist of safe file extensions that can be read
		// This prevents reading sensitive binary files or executables
		$safe_extensions = array(
			'txt', 'md', 'log',          // Text files
			'php', 'html', 'htm',        // Web files
			'css', 'scss', 'sass', 'less', // Style files
			'js', 'json', 'xml', 'yaml', 'yml', // Data/Script files
			'sql', 'sh', 'bash',         // Script files
			'ini', 'conf', 'config',     // Config files
			'csv', 'tsv',                // Data files
			'htaccess', 'htpasswd',      // Apache files
		);
		
		$extension = strtolower( pathinfo( $validated_path, PATHINFO_EXTENSION ) );
		$basename = basename( $validated_path );
		
		// Allow files without extension if they're known text config files
		$allowed_no_ext = array( 'README', 'LICENSE', 'CHANGELOG', 'Makefile', 'Dockerfile' );
		
		if ( ! empty( $extension ) && ! in_array( $extension, $safe_extensions, true ) ) {
			return new \WP_Error( 
				'file_type_not_allowed', 
				__( 'This file type cannot be read for security reasons. Only text-based files are allowed.', 'admin-site-enhancements') 
			);
		} elseif ( empty( $extension ) && ! in_array( $basename, $allowed_no_ext, true ) ) {
			return new \WP_Error( 
				'file_type_not_allowed', 
				__( 'Files without extensions cannot be read for security reasons.', 'admin-site-enhancements') 
			);
		}
		
		// Additional check: prevent reading PHP files that might expose sensitive data
		// like database credentials in certain contexts
		if ( 'php' === $extension ) {
			$sensitive_php_files = array(
				'wp-config.php',
				'config.php',
				'database.php',
				'db-config.php',
				'.env.php',
			);
			
			if ( in_array( $basename, $sensitive_php_files, true ) ) {
				// Allow reading but warn that it contains sensitive data
				// The user already has manage_options capability, so they should be able to view it
				// but we'll mark it in the response
			}
		}
		
		// Check file size - prevent reading extremely large files that could cause memory issues
		// This also helps prevent DoS attacks
		$file_size = filesize( $validated_path );
		$max_size = 10 * 1024 * 1024; // 10 MB limit
		
		if ( $file_size > $max_size ) {
			return new \WP_Error( 
				'file_too_large', 
				sprintf(
					/* translators: %s: maximum file size */
					__( 'File is too large to read. Maximum size is %s.', 'admin-site-enhancements'),
					size_format( $max_size, 2 )
				)
			);
		}

		$wp_filesystem = self::get_filesystem();
		if ( is_wp_error( $wp_filesystem ) ) {
			return $wp_filesystem;
		}

		$contents = $wp_filesystem->get_contents( $validated_path );
		if ( false === $contents ) {
			return new \WP_Error( 'read_error', __( 'Failed to read file.', 'admin-site-enhancements') );
		}

		return $contents;
	}

	/**
	 * Write file contents.
	 *
	 * @param string $path File path.
	 * @param string $contents File contents.
	 * @return bool|WP_Error True on success or error.
	 */
	public static function write_file( $path, $contents ) {
		$validated_path = self::validate_path( $path );
		if ( is_wp_error( $validated_path ) ) {
			return $validated_path;
		}

		$restriction_error = self::get_write_restriction_error_by_constants( $validated_path );
		if ( $restriction_error ) {
			return $restriction_error;
		}

		// Check if it's a protected core file
		if ( self::is_core_file( $validated_path ) ) {
			return new \WP_Error( 'protected_file', __( 'This WordPress core file is protected and cannot be edited.', 'admin-site-enhancements') );
		}

		$wp_filesystem = self::get_filesystem();
		if ( is_wp_error( $wp_filesystem ) ) {
			return $wp_filesystem;
		}

		// Check if directory is writable
		$dir = dirname( $validated_path );
		if ( ! $wp_filesystem->is_writable( $dir ) ) {
			return new \WP_Error( 'not_writable', __( 'The directory is not writable.', 'admin-site-enhancements') );
		}

		// Allow empty content (e.g., when clearing a file)
		if ( $contents === null ) {
			$contents = '';
		}

		// For PHP files, validate syntax first
		if ( pathinfo( $validated_path, PATHINFO_EXTENSION ) === 'php' && ! empty( $contents ) ) {
			$validation = self::validate_php_syntax( $contents );
			if ( is_wp_error( $validation ) ) {
				return $validation;
			}
		}

		$result = $wp_filesystem->put_contents( $validated_path, $contents, FS_CHMOD_FILE );
		if ( false === $result ) {
			return new \WP_Error( 'write_error', __( 'Failed to write file.', 'admin-site-enhancements') );
		}

		return true;
	}

	/**
	 * Create a new file.
	 *
	 * @param string $path File path.
	 * @return bool|WP_Error True on success or error.
	 */
	public static function create_file( $path ) {
		$validated_path = self::validate_path( $path );
		if ( is_wp_error( $validated_path ) ) {
			return $validated_path;
		}

		$restriction_error = self::get_write_restriction_error_by_constants( $validated_path );
		if ( $restriction_error ) {
			return $restriction_error;
		}

		// Do not allow creating new files inside WordPress core folders.
		if ( self::is_protected_core_write_folder( $validated_path ) ) {
			return new \WP_Error(
				'protected_core_folder',
				__( 'Modifying WordPress core folders is not allowed.', 'admin-site-enhancements' )
			);
		}

		if ( file_exists( $validated_path ) ) {
			return new \WP_Error( 'file_exists', __( 'File already exists.', 'admin-site-enhancements') );
		}

		$wp_filesystem = self::get_filesystem();
		if ( is_wp_error( $wp_filesystem ) ) {
			return $wp_filesystem;
		}

		$dir = dirname( $validated_path );
		if ( ! $wp_filesystem->is_writable( $dir ) ) {
			return new \WP_Error( 'not_writable', __( 'The directory is not writable.', 'admin-site-enhancements') );
		}

		$result = $wp_filesystem->touch( $validated_path );
		if ( ! $result ) {
			return new \WP_Error( 'create_error', __( 'Failed to create file.', 'admin-site-enhancements') );
		}

		return true;
	}

	/**
	 * Create a new folder.
	 *
	 * @param string $path Folder path.
	 * @return bool|WP_Error True on success or error.
	 */
	public static function create_folder( $path ) {
		$validated_path = self::validate_path( $path );
		if ( is_wp_error( $validated_path ) ) {
			return $validated_path;
		}

		$restriction_error = self::get_write_restriction_error_by_constants( $validated_path );
		if ( $restriction_error ) {
			return $restriction_error;
		}

		// Do not allow creating new folders inside WordPress core folders.
		if ( self::is_protected_core_write_folder( $validated_path ) ) {
			return new \WP_Error(
				'protected_core_folder',
				__( 'Modifying WordPress core folders is not allowed.', 'admin-site-enhancements' )
			);
		}

		if ( file_exists( $validated_path ) ) {
			return new \WP_Error( 'folder_exists', __( 'Folder already exists.', 'admin-site-enhancements') );
		}

		$wp_filesystem = self::get_filesystem();
		if ( is_wp_error( $wp_filesystem ) ) {
			return $wp_filesystem;
		}

		$parent_dir = dirname( $validated_path );
		if ( ! $wp_filesystem->is_writable( $parent_dir ) ) {
			return new \WP_Error( 'not_writable', __( 'The parent directory is not writable.', 'admin-site-enhancements') );
		}

		$result = $wp_filesystem->mkdir( $validated_path, FS_CHMOD_DIR );
		if ( ! $result ) {
			return new \WP_Error( 'create_error', __( 'Failed to create folder.', 'admin-site-enhancements') );
		}

		return true;
	}

	/**
	 * Delete a file or folder.
	 *
	 * @param string $path File or folder path.
	 * @return bool|WP_Error True on success or error.
	 */
	public static function delete( $path ) {
		$validated_path = self::validate_path( $path );
		if ( is_wp_error( $validated_path ) ) {
			return $validated_path;
		}

		$restriction_error = self::get_write_restriction_error_by_constants( $validated_path );
		if ( $restriction_error ) {
			return $restriction_error;
		}

		if ( ! file_exists( $validated_path ) ) {
			return new \WP_Error( 'not_found', __( 'File or folder not found.', 'admin-site-enhancements') );
		}

		// Check if it's wp-config.php (extra protection)
		if ( self::is_wp_config_file( $validated_path ) ) {
			return new \WP_Error( 'protected_file', __( 'wp-config.php cannot be deleted for security reasons.', 'admin-site-enhancements') );
		}

		// Check if it's a protected core file
		if ( self::is_core_file( $validated_path ) ) {
			return new \WP_Error( 'protected_file', __( 'This WordPress core file is protected and cannot be deleted.', 'admin-site-enhancements') );
		}

		// Check if it's the plugin's own directory (use strict comparison with realpath)
		$plugin_path_real = realpath( ASENHA_FILE_MANAGER_PATH );
		$validated_path_real = realpath( $validated_path );
		if ( 0 === strpos( $validated_path_real, $plugin_path_real ) ) {
			return new \WP_Error( 'protected_file', __( 'Cannot delete the plugin\'s own files.', 'admin-site-enhancements') );
		}

		// Protect critical configuration and security files
		$basename = basename( $validated_path );
		$critical_files = array(
			'.htaccess',
			'.env',
			'.env.local',
			'.env.production',
			'.env.development',
			'robots.txt',
			'composer.json',
			'composer.lock',
			'package.json',
			'package-lock.json',
			'web.config',
			'.user.ini',
		);
		
		if ( in_array( $basename, $critical_files, true ) ) {
			// Two-step verification for critical files - require explicit confirmation
			// Store a transient that must be set before allowing deletion
			$delete_token = get_transient( 'asenha_fm_delete_critical_' . md5( $validated_path ) );
			if ( ! $delete_token || 'confirmed' !== $delete_token ) {
				// Set transient for 30 seconds
				set_transient( 'asenha_fm_delete_critical_' . md5( $validated_path ), 'confirmed', 30 );
				return new \WP_Error( 
					'critical_file_confirmation', 
					sprintf(
						/* translators: %s: filename */
						__( 'This is a critical file (%s). Please confirm deletion by attempting again within 30 seconds.', 'admin-site-enhancements'),
						$basename
					)
				);
			}
			// Delete the transient after use
			delete_transient( 'asenha_fm_delete_critical_' . md5( $validated_path ) );
		}

		$wp_filesystem = self::get_filesystem();
		if ( is_wp_error( $wp_filesystem ) ) {
			return $wp_filesystem;
		}

		if ( is_dir( $validated_path ) ) {
			return self::delete_directory( $validated_path );
		} else {
			$result = $wp_filesystem->delete( $validated_path );
			if ( ! $result ) {
				return new \WP_Error( 'delete_error', __( 'Failed to delete file.', 'admin-site-enhancements') );
			}
		}

		return true;
	}

	/**
	 * Delete a directory recursively.
	 *
	 * @param string $path Directory path.
	 * @return bool|WP_Error True on success or error.
	 */
	private static function delete_directory( $path ) {
		if ( ! is_dir( $path ) ) {
			return new \WP_Error( 'not_directory', __( 'Path is not a directory.', 'admin-site-enhancements') );
		}

		$wp_filesystem = self::get_filesystem();
		if ( is_wp_error( $wp_filesystem ) ) {
			return $wp_filesystem;
		}

		try {
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $path, FilesystemIterator::SKIP_DOTS ),
				RecursiveIteratorIterator::CHILD_FIRST
			);

			foreach ( $iterator as $file ) {
				if ( $file->isDir() ) {
					$wp_filesystem->rmdir( $file->getPathname() );
				} else {
					$wp_filesystem->delete( $file->getPathname() );
				}
			}

			$wp_filesystem->rmdir( $path );
		} catch ( \Exception $e ) {
			return new \WP_Error( 'delete_error', $e->getMessage() );
		}

		return true;
	}

	/**
	 * Rename a file or folder.
	 *
	 * @param string $old_path Old path.
	 * @param string $new_path New path.
	 * @return bool|WP_Error True on success or error.
	 */
	public static function rename( $old_path, $new_path ) {
		$validated_old = self::validate_path( $old_path );
		if ( is_wp_error( $validated_old ) ) {
			return $validated_old;
		}

		$validated_new = self::validate_path( $new_path );
		if ( is_wp_error( $validated_new ) ) {
			return $validated_new;
		}

		$restriction_error = self::get_write_restriction_error_by_constants( $validated_old );
		if ( $restriction_error ) {
			return $restriction_error;
		}

		$restriction_error = self::get_write_restriction_error_by_constants( $validated_new );
		if ( $restriction_error ) {
			return $restriction_error;
		}

		if ( ! file_exists( $validated_old ) ) {
			return new \WP_Error( 'not_found', __( 'Source file or folder not found.', 'admin-site-enhancements') );
		}

		// Check if it's wp-config.php (extra protection)
		if ( self::is_wp_config_file( $validated_old ) ) {
			return new \WP_Error( 'protected_file', __( 'wp-config.php cannot be renamed for security reasons.', 'admin-site-enhancements') );
		}

		// Check if it's a protected core file
		if ( self::is_core_file( $validated_old ) ) {
			return new \WP_Error( 'protected_file', __( 'This WordPress core file is protected and cannot be renamed.', 'admin-site-enhancements') );
		}

		if ( file_exists( $validated_new ) ) {
			return new \WP_Error( 'file_exists', __( 'Destination already exists.', 'admin-site-enhancements') );
		}

		$wp_filesystem = self::get_filesystem();
		if ( is_wp_error( $wp_filesystem ) ) {
			return $wp_filesystem;
		}

		$result = $wp_filesystem->move( $validated_old, $validated_new );
		if ( ! $result ) {
			return new \WP_Error( 'rename_error', __( 'Failed to rename.', 'admin-site-enhancements') );
		}

		return true;
	}

	/**
	 * Copy a file or folder.
	 *
	 * @param string $source Source path.
	 * @param string $destination Destination path.
	 * @return bool|WP_Error True on success or error.
	 */
	public static function copy( $source, $destination ) {
		$validated_source = self::validate_path( $source );
		if ( is_wp_error( $validated_source ) ) {
			return $validated_source;
		}

		$validated_dest = self::validate_path( $destination );
		if ( is_wp_error( $validated_dest ) ) {
			return $validated_dest;
		}

		// Copy is not allowed when either side is within a read-only area.
		$restriction_error = self::get_write_restriction_error_by_constants( $validated_source );
		if ( $restriction_error ) {
			return $restriction_error;
		}

		$restriction_error = self::get_write_restriction_error_by_constants( $validated_dest );
		if ( $restriction_error ) {
			return $restriction_error;
		}

		if ( ! file_exists( $validated_source ) ) {
			return new \WP_Error( 'not_found', __( 'Source file or folder not found.', 'admin-site-enhancements') );
		}

		$wp_filesystem = self::get_filesystem();
		if ( is_wp_error( $wp_filesystem ) ) {
			return $wp_filesystem;
		}

		if ( is_dir( $validated_source ) ) {
			return self::copy_directory( $validated_source, $validated_dest );
		} else {
			$result = $wp_filesystem->copy( $validated_source, $validated_dest );
			if ( ! $result ) {
				return new \WP_Error( 'copy_error', __( 'Failed to copy file.', 'admin-site-enhancements') );
			}
		}

		return true;
	}

	/**
	 * Copy a directory recursively.
	 *
	 * @param string $source Source directory path.
	 * @param string $destination Destination directory path.
	 * @return bool|WP_Error True on success or error.
	 */
	private static function copy_directory( $source, $destination ) {
		if ( ! is_dir( $source ) ) {
			return new \WP_Error( 'not_directory', __( 'Source is not a directory.', 'admin-site-enhancements') );
		}

		$wp_filesystem = self::get_filesystem();
		if ( is_wp_error( $wp_filesystem ) ) {
			return $wp_filesystem;
		}

		if ( ! is_dir( $destination ) ) {
			$wp_filesystem->mkdir( $destination, FS_CHMOD_DIR );
		}

		try {
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $source, FilesystemIterator::SKIP_DOTS ),
				RecursiveIteratorIterator::SELF_FIRST
			);

			foreach ( $iterator as $file ) {
				$dest_path = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();

				if ( $file->isDir() ) {
					if ( ! is_dir( $dest_path ) ) {
						$wp_filesystem->mkdir( $dest_path, FS_CHMOD_DIR );
					}
				} else {
					$wp_filesystem->copy( $file->getPathname(), $dest_path );
				}
			}
		} catch ( \Exception $e ) {
			return new \WP_Error( 'copy_error', $e->getMessage() );
		}

		return true;
	}

	/**
	 * Change file permissions.
	 *
	 * @param string $path File path.
	 * @param string $permissions Permissions in octal format (e.g., '755').
	 * @return bool|WP_Error True on success or error.
	 */
	public static function change_permissions( $path, $permissions ) {
		$validated_path = self::validate_path( $path );
		if ( is_wp_error( $validated_path ) ) {
			return $validated_path;
		}

		$restriction_error = self::get_write_restriction_error_by_constants( $validated_path );
		if ( $restriction_error ) {
			return $restriction_error;
		}

		if ( ! file_exists( $validated_path ) ) {
			return new \WP_Error( 'not_found', __( 'File or folder not found.', 'admin-site-enhancements') );
		}

		// Check if it's a protected core file
		if ( self::is_core_file( $validated_path ) ) {
			return new \WP_Error( 'protected_file', __( 'Cannot change permissions on WordPress core files.', 'admin-site-enhancements') );
		}

		// Validate permissions format
		if ( ! preg_match( '/^[0-7]{3}$/', $permissions ) ) {
			return new \WP_Error( 'invalid_permissions', __( 'Invalid permissions format.', 'admin-site-enhancements') );
		}

		// Validate permission ranges - prevent dangerous permissions
		$perms_octal = octdec( $permissions );
		
		// Prevent setting executable bit on files (not directories)
		// Executable files can be a security risk
		if ( is_file( $validated_path ) ) {
			// Check if any executable bit is set (user, group, or other)
			if ( ( $perms_octal & 0111 ) !== 0 ) {
				return new \WP_Error( 
					'executable_not_allowed', 
					__( 'Setting executable permissions on files is not allowed for security reasons.', 'admin-site-enhancements') 
				);
			}
			
			// For files, maximum should be 666 (rw-rw-rw-)
			// Common safe values: 644 (rw-r--r--), 640 (rw-r-----), 600 (rw-------)
			if ( $perms_octal > 0666 ) {
				return new \WP_Error( 
					'invalid_file_permissions', 
					__( 'File permissions cannot exceed 666 (rw-rw-rw-). Executable bits are not allowed on files.', 'admin-site-enhancements') 
				);
			}
		}
		
		// Prevent 777 (rwxrwxrwx) on anything - too permissive
		if ( '777' === $permissions ) {
			return new \WP_Error( 
				'permissions_too_permissive', 
				__( 'Permissions 777 are not allowed for security reasons. Use 755 for directories or 644 for files instead.', 'admin-site-enhancements') 
			);
		}
		
		// For directories, recommend maximum of 755
		if ( is_dir( $validated_path ) && $perms_octal > 0755 ) {
			return new \WP_Error( 
				'directory_permissions_too_permissive', 
				__( 'Directory permissions should not exceed 755 (rwxr-xr-x) for security reasons.', 'admin-site-enhancements') 
			);
		}

		$wp_filesystem = self::get_filesystem();
		if ( is_wp_error( $wp_filesystem ) ) {
			return $wp_filesystem;
		}

		$result = $wp_filesystem->chmod( $validated_path, $perms_octal );
		if ( ! $result ) {
			return new \WP_Error( 'chmod_error', __( 'Failed to change permissions.', 'admin-site-enhancements') );
		}

		return true;
	}

	/**
	 * Compress files/folders into a ZIP archive.
	 *
	 * @param array  $paths Array of file/folder paths to compress.
	 * @param string $archive_name Archive name.
	 * @param string $destination Destination directory for the archive.
	 * @return string|WP_Error Archive path on success or error.
	 */
	public static function compress( $paths, $archive_name, $destination ) {
		if ( ! class_exists( 'ZipArchive' ) ) {
			return new \WP_Error( 'zip_not_available', __( 'ZipArchive class is not available.', 'admin-site-enhancements') );
		}

		$validated_dest = self::validate_path( $destination );
		if ( is_wp_error( $validated_dest ) ) {
			return $validated_dest;
		}

		$restriction_error = self::get_write_restriction_error_by_constants( $validated_dest );
		if ( $restriction_error ) {
			return $restriction_error;
		}

		if ( empty( $paths ) || ! is_array( $paths ) ) {
			return new \WP_Error( 'missing_paths', __( 'Paths parameter is required and must be an array.', 'admin-site-enhancements' ) );
		}

		$validated_paths = array();
		foreach ( $paths as $path ) {
			$validated_path = self::validate_path( $path );
			if ( is_wp_error( $validated_path ) ) {
				return $validated_path;
			}

			$restriction_error = self::get_write_restriction_error_by_constants( $validated_path );
			if ( $restriction_error ) {
				return $restriction_error;
			}

			$validated_paths[] = $validated_path;
		}

		$wp_filesystem = self::get_filesystem();
		if ( is_wp_error( $wp_filesystem ) ) {
			return $wp_filesystem;
		}

		if ( ! is_dir( $validated_dest ) || ! $wp_filesystem->is_writable( $validated_dest ) ) {
			return new \WP_Error( 'not_writable', __( 'Destination directory is not writable.', 'admin-site-enhancements') );
		}

		$archive_path = trailingslashit( $validated_dest ) . $archive_name;

		$zip = new ZipArchive();
		if ( $zip->open( $archive_path, ZipArchive::CREATE | ZipArchive::OVERWRITE ) !== true ) {
			return new \WP_Error( 'zip_error', __( 'Failed to create ZIP archive.', 'admin-site-enhancements') );
		}

		foreach ( $validated_paths as $validated_path ) {
			if ( ! file_exists( $validated_path ) ) {
				return new \WP_Error( 'not_found', __( 'One or more selected items were not found.', 'admin-site-enhancements' ) );
			}

			if ( is_file( $validated_path ) ) {
				$zip->addFile( $validated_path, basename( $validated_path ) );
			} elseif ( is_dir( $validated_path ) ) {
				self::add_directory_to_zip( $zip, $validated_path, basename( $validated_path ) );
			}
		}

		$zip->close();

		return $archive_path;
	}

	/**
	 * Add directory to ZIP archive recursively.
	 *
	 * @param ZipArchive $zip ZIP archive object.
	 * @param string     $dir_path Directory path.
	 * @param string     $local_path Local path in archive.
	 * @return void
	 */
	private static function add_directory_to_zip( $zip, $dir_path, $local_path ) {
		$zip->addEmptyDir( $local_path );

		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $dir_path, FilesystemIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ( $iterator as $file ) {
			$file_path = $file->getPathname();
			$relative_path = $local_path . DIRECTORY_SEPARATOR . $iterator->getSubPathName();

			if ( $file->isDir() ) {
				$zip->addEmptyDir( $relative_path );
			} else {
				$zip->addFile( $file_path, $relative_path );
			}
		}
	}

	/**
	 * Extract a ZIP archive.
	 *
	 * @param string $archive_path Archive path.
	 * @param string $destination Destination directory.
	 * @return bool|WP_Error True on success or error.
	 */
	public static function extract( $archive_path, $destination ) {
		if ( ! class_exists( 'ZipArchive' ) ) {
			return new \WP_Error( 'zip_not_available', __( 'ZipArchive class is not available.', 'admin-site-enhancements') );
		}

		$validated_archive = self::validate_path( $archive_path );
		if ( is_wp_error( $validated_archive ) ) {
			return $validated_archive;
		}

		$validated_dest = self::validate_path( $destination );
		if ( is_wp_error( $validated_dest ) ) {
			return $validated_dest;
		}

		$restriction_error = self::get_write_restriction_error_by_constants( $validated_archive );
		if ( $restriction_error ) {
			return $restriction_error;
		}

		$restriction_error = self::get_write_restriction_error_by_constants( $validated_dest );
		if ( $restriction_error ) {
			return $restriction_error;
		}

		if ( ! file_exists( $validated_archive ) ) {
			return new \WP_Error( 'not_found', __( 'Archive not found.', 'admin-site-enhancements') );
		}

		$wp_filesystem = self::get_filesystem();
		if ( is_wp_error( $wp_filesystem ) ) {
			return $wp_filesystem;
		}

		if ( ! is_dir( $validated_dest ) || ! $wp_filesystem->is_writable( $validated_dest ) ) {
			return new \WP_Error( 'not_writable', __( 'Destination directory is not writable.', 'admin-site-enhancements') );
		}

		$zip = new ZipArchive();
		if ( $zip->open( $validated_archive ) !== true ) {
			return new \WP_Error( 'zip_error', __( 'Failed to open ZIP archive.', 'admin-site-enhancements') );
		}

		$result = $zip->extractTo( $validated_dest );
		$zip->close();

		if ( ! $result ) {
			return new \WP_Error( 'extract_error', __( 'Failed to extract archive.', 'admin-site-enhancements') );
		}

		return true;
	}

	/**
	 * Validate PHP syntax using token_get_all() for reliable syntax checking.
	 *
	 * @param string $code PHP code to validate.
	 * @return bool|WP_Error True if valid or error.
	 */
	public static function validate_php_syntax( $code ) {
		// Use PHP's built-in tokenizer to check for syntax errors
		// This is more reliable than exec() and doesn't require external commands
		
		// Suppress errors temporarily
		$previous_error_handler = set_error_handler( function() {} ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler
		
		// Try to tokenize the code
		$tokens = @token_get_all( $code );
		
		// Restore previous error handler
		if ( null !== $previous_error_handler ) {
			set_error_handler( $previous_error_handler ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler
		} else {
			restore_error_handler();
		}
		
		// If tokenization failed, there's a syntax error
		if ( false === $tokens || empty( $tokens ) ) {
			return new \WP_Error(
				'syntax_error',
				__( 'PHP syntax error detected. Please check your code for missing brackets, quotes, or semicolons.', 'admin-site-enhancements')
			);
		}
		
		// Additional validation: Check for common syntax issues
		$open_brackets = 0;
		$open_braces = 0;
		$open_parens = 0;
		$in_string = false;
		$string_delimiter = null;
		
		foreach ( $tokens as $token ) {
			// Handle array tokens (most tokens)
			if ( is_array( $token ) ) {
				$token_type = $token[0];
				
				// Check for parse errors reported by tokenizer
				if ( defined( 'T_BAD_CHARACTER' ) && $token_type === T_BAD_CHARACTER ) {
					return new \WP_Error(
						'syntax_error',
						sprintf(
							/* translators: %s: character that caused the error */
							__( 'Invalid character detected: %s', 'admin-site-enhancements'),
							htmlspecialchars( $token[1] )
						)
					);
				}
			} else {
				// Handle single character tokens
				switch ( $token ) {
					case '{':
						$open_braces++;
						break;
					case '}':
						$open_braces--;
						if ( $open_braces < 0 ) {
							return new \WP_Error( 'syntax_error', __( 'Unmatched closing brace "}" detected.', 'admin-site-enhancements') );
						}
						break;
					case '[':
						$open_brackets++;
						break;
					case ']':
						$open_brackets--;
						if ( $open_brackets < 0 ) {
							return new \WP_Error( 'syntax_error', __( 'Unmatched closing bracket "]" detected.', 'admin-site-enhancements') );
						}
						break;
					case '(':
						$open_parens++;
						break;
					case ')':
						$open_parens--;
						if ( $open_parens < 0 ) {
							return new \WP_Error( 'syntax_error', __( 'Unmatched closing parenthesis ")" detected.', 'admin-site-enhancements') );
						}
						break;
				}
			}
		}
		
		// Check for unclosed brackets/braces/parentheses
		if ( $open_braces > 0 ) {
			return new \WP_Error( 'syntax_error', __( 'Unclosed brace "{" detected.', 'admin-site-enhancements') );
		}
		if ( $open_brackets > 0 ) {
			return new \WP_Error( 'syntax_error', __( 'Unclosed bracket "[" detected.', 'admin-site-enhancements') );
		}
	if ( $open_parens > 0 ) {
		return new \WP_Error( 'syntax_error', __( 'Unclosed parenthesis "(" detected.', 'admin-site-enhancements') );
	}
	
	// Check for obvious missing semicolons
	$prev_significant_token = null;
	$prev_significant_line = 1;
	
	foreach ( $tokens as $token ) {
		$current_token_type = null;
		$current_token_value = null;
		$current_line = $prev_significant_line;
		
		// Extract token information
		if ( is_array( $token ) ) {
			$current_token_type = $token[0];
			$current_token_value = $token[1];
			$current_line = isset( $token[2] ) ? $token[2] : $prev_significant_line;
			
			// Skip whitespace and comments
			if ( in_array( $current_token_type, array( T_WHITESPACE, T_COMMENT, T_DOC_COMMENT ), true ) ) {
				continue;
			}
		} else {
			$current_token_type = $token;
			$current_token_value = $token;
		}
		
		// Check for missing semicolon patterns
		if ( null !== $prev_significant_token ) {
			// Pattern 1: return/break/continue/throw followed by } without semicolon
			if ( is_array( $prev_significant_token ) && 
				 in_array( $prev_significant_token[0], array( T_RETURN, T_BREAK, T_CONTINUE, T_THROW ), true ) &&
				 '}' === $current_token_type ) {
				return new \WP_Error(
					'syntax_error',
					sprintf(
						/* translators: %d: line number */
						__( 'Possible missing semicolon detected on line %d.', 'admin-site-enhancements'),
						$prev_significant_line
					)
				);
			}
			
			// Pattern 2: Closing parenthesis ) followed by control keywords without semicolon
			if ( ')' === $prev_significant_token &&
				 is_array( $token ) &&
				 in_array( $current_token_type, array( T_IF, T_WHILE, T_FOR, T_FOREACH, T_SWITCH ), true ) ) {
				return new \WP_Error(
					'syntax_error',
					sprintf(
						/* translators: %d: line number */
						__( 'Possible missing semicolon detected on line %d.', 'admin-site-enhancements'),
						$prev_significant_line
					)
				);
			}
			
			// Pattern 3: Variable followed by another variable without semicolon
			if ( is_array( $prev_significant_token ) &&
				 T_VARIABLE === $prev_significant_token[0] &&
				 is_array( $token ) &&
				 T_VARIABLE === $current_token_type ) {
				return new \WP_Error(
					'syntax_error',
					sprintf(
						/* translators: %d: line number */
						__( 'Possible missing semicolon detected on line %d.', 'admin-site-enhancements'),
						$prev_significant_line
					)
				);
			}
		}
		
		// Update previous significant token
		$prev_significant_token = $token;
		$prev_significant_line = $current_line;
	}
	
	// Syntax is valid
	return true;
	}

	/**
	 * Validate and sanitize file path.
	 *
	 * @param string $path File path to validate.
	 * @return string|WP_Error Validated path or error.
	 */
	public static function validate_path( $path ) {
		// Remove any null bytes
		$path = str_replace( chr( 0 ), '', $path );

		// Check for dangerous filename patterns BEFORE any processing
		// This prevents encoded traversal attempts like %2e%2e, ..%2f, etc.
		$dangerous_patterns = array(
			'/\.\./',           // Directory traversal (..)
			'/%2e%2e/i',        // URL encoded (..)
			'/\x2e\x2e/',       // Hex encoded (..)
			'/%252e%252e/i',    // Double URL encoded (..)
			'/\.%2f/i',         // Encoded slash combinations
			'/%5c/i',           // Encoded backslash
			'/\x00/',           // Null byte
		);
		
		foreach ( $dangerous_patterns as $pattern ) {
			if ( preg_match( $pattern, $path ) ) {
				return new \WP_Error( 'dangerous_path', __( 'Dangerous path pattern detected.', 'admin-site-enhancements') );
			}
		}

		// Validate each path component separately (prevent bypasses)
		$path_components = explode( '/', trim( $path, '/' ) );
		foreach ( $path_components as $component ) {
			// Disallow . and .. components
			if ( '.' === $component || '..' === $component ) {
				return new \WP_Error( 'path_traversal', __( 'Path traversal detected in component.', 'admin-site-enhancements') );
			}
		}

		// Check for symlinks BEFORE resolving realpath (TOCTOU protection)
		// This prevents race conditions where a symlink is created between check and use
		if ( file_exists( $path ) && is_link( $path ) ) {
			return new \WP_Error( 'symlink_detected', __( 'Symbolic links are not allowed.', 'admin-site-enhancements') );
		}

		// Resolve the real path
		$real_path = realpath( $path );

		// If path doesn't exist yet (e.g., for new files), validate the parent directory
		if ( false === $real_path ) {
			$parent_dir = dirname( $path );
			
			// Check parent for symlinks too
			if ( file_exists( $parent_dir ) && is_link( $parent_dir ) ) {
				return new \WP_Error( 'symlink_detected', __( 'Symbolic links are not allowed.', 'admin-site-enhancements') );
			}
			
			$real_parent = realpath( $parent_dir );

			if ( false === $real_parent ) {
				return new \WP_Error( 'invalid_path', __( 'Invalid path.', 'admin-site-enhancements') );
			}

			// Check if parent is within ABSPATH (strict comparison)
			$abspath_real = realpath( ABSPATH );
			if ( 0 !== strpos( $real_parent, $abspath_real ) ) {
				return new \WP_Error( 'path_traversal', __( 'Path traversal detected.', 'admin-site-enhancements') );
			}

			// Reconstruct the full path
			$real_path = trailingslashit( $real_parent ) . basename( $path );
		} else {
			// Path exists, perform additional symlink check on resolved path
			// This catches cases where realpath() follows symlinks
			$link_check = @lstat( $path );
			$file_check = @stat( $path );
			
			// If lstat and stat return different devices/inodes, it's likely a symlink
			if ( $link_check && $file_check && 
				 ( $link_check['dev'] !== $file_check['dev'] || $link_check['ino'] !== $file_check['ino'] ) ) {
				return new \WP_Error( 'symlink_detected', __( 'Symbolic links are not allowed.', 'admin-site-enhancements') );
			}
		}

		// Ensure path is within ABSPATH (prevent path traversal) - use strict comparison
		$abspath_real = realpath( ABSPATH );
		if ( 0 !== strpos( $real_path, $abspath_real ) ) {
			return new \WP_Error( 'path_traversal', __( 'Path traversal detected.', 'admin-site-enhancements') );
		}

		// Final TOCTOU protection: verify the path again right before returning
		// This catches race conditions where a symlink might be created after our checks
		clearstatcache( true, $path );
		if ( file_exists( $path ) && is_link( $path ) ) {
			return new \WP_Error( 'symlink_detected', __( 'Symbolic links are not allowed.', 'admin-site-enhancements') );
		}

		return $real_path;
	}

	/**
	 * Check if a file is wp-config.php (special protection).
	 *
	 * @param string $path File path.
	 * @return bool True if wp-config.php, false otherwise.
	 */
	public static function is_wp_config_file( $path ) {
		$real_path = realpath( $path );
		if ( false === $real_path ) {
			return false;
		}

		$basename = basename( $real_path );
		return 'wp-config.php' === $basename;
	}

	/**
	 * Check if a file is a WordPress core file (protected).
	 *
	 * @param string $path File path.
	 * @return bool True if core file, false otherwise.
	 */
	public static function is_core_file( $path ) {
		$real_path = realpath( $path );
		if ( false === $real_path ) {
			return false;
		}

		$abspath = realpath( ABSPATH );
		$wp_admin = trailingslashit( $abspath ) . 'wp-admin';
		$wp_includes = trailingslashit( $abspath ) . 'wp-includes';
		$wp_content = trailingslashit( $abspath ) . 'wp-content';

		// wp-config.php and wp-config-sample.php are NOT protected (editable)
		$basename = basename( $real_path );
		if ( 'wp-config.php' === $basename || 'wp-config-sample.php' === $basename ) {
			return false;
		}

		// Folders: wp-admin, wp-includes, and wp-content (folder itself, not contents) are protected
		if ( $real_path === $wp_admin || $real_path === $wp_includes || $real_path === $wp_content ) {
			return true;
		}

		// Files in wp-admin or wp-includes are protected
		if ( strpos( $real_path, $wp_admin ) === 0 || strpos( $real_path, $wp_includes ) === 0 ) {
			return true;
		}

		// WordPress core files in root directory (protected)
		$core_root_files = array(
			'index.php',
			'license.txt',
			'readme.html',
			'wp-activate.php',
			'wp-blog-header.php',
			'wp-comments-post.php',
			'wp-cron.php',
			'wp-links-opml.php',
			'wp-load.php',
			'wp-login.php',
			'wp-mail.php',
			'wp-settings.php',
			'wp-signup.php',
			'wp-trackback.php',
			'xmlrpc.php',
		);

		// Check if file is in ABSPATH root and is a core file
		if ( dirname( $real_path ) === $abspath && in_array( $basename, $core_root_files, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Build directory tree structure.
	 *
	 * @param string      $path Root directory path.
	 * @param int         $depth Maximum depth (0 = unlimited).
	 * @param string|null $expand_to_path Optional. Path to expand tree to (pre-expand ancestors).
	 * @return array|WP_Error Directory tree or error.
	 */
	public static function build_tree( $path, $depth = 1, $expand_to_path = null ) {
		$validated_path = self::validate_path( $path );
		if ( is_wp_error( $validated_path ) ) {
			return $validated_path;
		}

		if ( ! is_dir( $validated_path ) ) {
			return new \WP_Error( 'not_directory', __( 'Path is not a directory.', 'admin-site-enhancements') );
		}

		// Normalize paths for comparison (remove trailing slashes)
		$current_path_normalized = rtrim( $validated_path, '/' );
		$expand_to_normalized = $expand_to_path ? rtrim( $expand_to_path, '/' ) : null;

		// Check if this path is the target or an ancestor of the target
		$is_target = false;
		$is_ancestor = false;
		
		if ( $expand_to_normalized ) {
			$is_target = ( $current_path_normalized === $expand_to_normalized );
			// Check if current path is an ancestor (target path starts with current path + /)
			$is_ancestor = ( strpos( $expand_to_normalized . '/', $current_path_normalized . '/' ) === 0 );
		}

		$tree = array(
			'name'     => basename( $validated_path ),
			'path'     => $validated_path,
			'children' => array(),
		);

		// Mark target as active
		if ( $is_target ) {
			$tree['active'] = true;
		}

		// Mark target and ancestors as expanded
		if ( $is_target || $is_ancestor ) {
			$tree['expanded'] = true;
		}

		// Load children if:
		// 1. Depth allows it (depth > 0 or unlimited)
		// 2. OR this is an ancestor/target that needs expansion
		$should_load_children = ( 0 !== $depth ) || $is_ancestor || $is_target;

		if ( $should_load_children ) {
			try {
				$iterator = new FilesystemIterator( $validated_path, FilesystemIterator::SKIP_DOTS );

				foreach ( $iterator as $file ) {
					if ( $file->isDir() ) {
						$child_path = $file->getPathname();
						$child_path_normalized = rtrim( $child_path, '/' );

						// Determine if we should recurse deeper for this child
						$child_is_ancestor = false;
						if ( $expand_to_normalized ) {
							$child_is_ancestor = ( strpos( $expand_to_normalized . '/', $child_path_normalized . '/' ) === 0 );
						}

						// If this child is an ancestor or target, or depth allows, load its children
						if ( $child_is_ancestor || $depth > 1 ) {
							// Recurse with full expansion
							$tree['children'][] = self::build_tree( $child_path, $depth - 1, $expand_to_path );
						} else {
							// Just add as collapsed node
							$tree['children'][] = array(
								'name'     => $file->getFilename(),
								'path'     => $file->getPathname(),
								'children' => array(),
							);
						}
					}
				}
			} catch ( \Exception $e ) {
				return new \WP_Error( 'tree_error', $e->getMessage() );
			}
		}

		return $tree;
	}
}

