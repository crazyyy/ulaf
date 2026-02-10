<?php
/**
 * Class: Responsible for File editing operations.
 *
 * Edit, adds, deletes files.
 *
 * @package advanced-analytics
 *
 * @since 4.0.0
 */

declare(strict_types=1);

namespace ADVAN\Views;

use ADVAN\Lists\Logs_List;
use ADVAN\Helpers\Settings;
use ADVAN\Helpers\WP_Helper;
use ADVAN\Helpers\File_Helper;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\ADVAN\Views\File_Editor' ) ) {

	/**
	 * Class File_Editor
	 */
	class File_Editor {
		public const FILE_EDITOR_MENU_SLUG = 'advan_file_editor';

		public const PAGE_SLUG = ADVAN_INNER_SLUG . '_page_advan_file_editor';

		public const BASE_DIR = ABSPATH;

		/**
		 * Storage root directory
		 *
		 * @var string
		 *
		 * @since 4.0.0
		 */
		private static $storage_root;
		/**
		 * Trash directory
		 *
		 * @var string
		 *
		 * @since 4.0.0
		 */
		private static $trash_dir;
		/**
		 * Backup directory
		 *
		 * @var string
		 *
		 * @since 4.0.0
		 */
		private static $backup_dir;
		/**
		 * Meta file
		 *
		 * @var string
		 *
		 * @since 4.0.0
		 */
		private static $meta_file;
		/**
		 * Last deleted file
		 *
		 * @var string|null
		 *
		 * @since 4.0.0
		 */
		private static $last_deleted = null;
		/**
		 * Maximum number of backups
		 *
		 * @var int
		 *
		 * @since 4.0.0
		 */
		private static $max_backups = 5;

		/**
		 * Inits the class hooks
		 *
		 * @return void
		 *
		 * @since 4.0.0
		 */
		public static function init() {

			self::load_meta();

			\add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		}

		/**
		 * Gets the upload directory info
		 *
		 * @return array Upload directory info.
		 *
		 * @since 4.0.0
		 */
		public static function get_upload_dir() {
			return \wp_upload_dir();
		}

		/**
		 * Gets the storage root directory
		 *
		 * @return string Storage root directory.
		 *
		 * @since 4.0.0
		 */
		public static function get_storage_root() {
			if ( ! isset( self::$storage_root ) ) {
				self::$storage_root = self::get_upload_dir()['basedir'] . \DIRECTORY_SEPARATOR . self::FILE_EDITOR_MENU_SLUG;
			}

			return self::$storage_root;
		}

		/**
		 * Gets the trash directory
		 *
		 * @return string Trash directory.
		 *
		 * @since 4.0.0
		 */
		public static function get_trash_dir() {
			if ( ! isset( self::$trash_dir ) ) {
				self::$trash_dir = self::get_storage_root() . \DIRECTORY_SEPARATOR . 'trash';
				@\wp_mkdir_p( self::$trash_dir );
				self::harden_directory( self::$trash_dir );
			}

			return self::$trash_dir;
		}
		/**
		 * Gets the backup directory
		 *
		 * @return string Backup directory.
		 *
		 * @since 4.0.0
		 */
		public static function get_backup_dir() {
			if ( ! isset( self::$backup_dir ) ) {
				self::$backup_dir = self::get_storage_root() . \DIRECTORY_SEPARATOR . 'backups';
				@\wp_mkdir_p( self::$backup_dir );
				self::harden_directory( self::$backup_dir );
			}

			return self::$backup_dir;
		}

		/**
		 * Adds basic hardening to a storage directory to prevent direct web access.
		 *
		 * @param string $dir Directory absolute path.
		 *
		 * @return void
		 *
		 * @since 4.0.1
		 */
		private static function harden_directory( $dir ) {
			if ( ! is_dir( $dir ) ) {
				return;
			}

			File_Helper::create_htaccess_file( $dir );
			File_Helper::create_index_file( $dir );
		}

		/**
		 * Gets the meta file path
		 *
		 * @return string Meta file path.
		 *
		 * @since 4.0.0
		 */
		public static function get_meta_file() {
			if ( ! isset( self::$meta_file ) ) {
				self::$meta_file = self::get_trash_dir() . \DIRECTORY_SEPARATOR . '.meta.json';
			}

			return self::$meta_file;
		}

		/**
		 * Gets the last deleted file info
		 *
		 * @return array|null Last deleted file info.
		 *
		 * @since 4.0.0
		 */
		public static function get_last_deleted() {
			if ( null === self::$last_deleted ) {
				self::load_meta();
			}

			return self::$last_deleted;
		}

		/**
		 * Registers the admin menu
		 *
		 * @return void
		 *
		 * @since 4.0.0
		 */
		public static function menu_add() {

			/* File Editor */
			$file_editor_hook = \add_submenu_page(
				Logs_List::MENU_SLUG,
				ADVAN_INNER_NAME,
				\esc_html__( 'File Editor', '0-day-analytics' ),
				( ( Settings::get_option( 'menu_admins_only' ) ) ? 'manage_options' : 'read' ), // No capability requirement.
				self::FILE_EDITOR_MENU_SLUG,
				array( self::class, 'render_page' ),
				8
			);

			\add_action( 'load-' . $file_editor_hook, array( Settings::class, 'aadvana_common_help' ) );
		}

		/**
		 * Enqueues the required assets
		 *
		 * @param string $hook The current admin page.
		 *
		 * @return void
		 *
		 * @since 4.0.0
		 */
		public static function enqueue_assets( $hook ) {
			if ( self::PAGE_SLUG !== $hook ) {
				return;
			}

			\wp_enqueue_code_editor( array( 'type' => 'text/html' ) );
			\wp_enqueue_script( 'wp-theme-plugin-editor' );
			\wp_enqueue_style( 'wp-codemirror' );

			\wp_enqueue_style( 'advan-file-editor-style', \ADVAN_PLUGIN_ROOT_URL . 'css' . \DIRECTORY_SEPARATOR . 'wfe.css', array(), ADVAN_VERSION );
			\wp_enqueue_script( 'advan-file-editor-script', \ADVAN_PLUGIN_ROOT_URL . 'js' . \DIRECTORY_SEPARATOR . 'admin' . \DIRECTORY_SEPARATOR . 'wfe.js', array( 'jquery', 'wp-codemirror', 'wp-i18n' ), ADVAN_VERSION, true );

			\wp_localize_script(
				'advan-file-editor-script',
				'AFE_Ajax',
				array(
					'ajax_url' => \admin_url( 'admin-ajax.php' ),
					'nonce'    => \wp_create_nonce( 'advan_file_editor_nonce' ),
					'base'     => self::BASE_DIR,
				)
			);
		}

		/**
		 * Renders the main page
		 *
		 * @return void
		 *
		 * @since 4.0.0
		 */
		public static function render_page() {
			// Capability guard: only allow administrators (or users with equivalent capability).
			if ( ! \current_user_can( 'manage_options' ) ) {
				\wp_die( \esc_html__( 'You do not have permission to manage files.', '0-day-analytics' ) );
			}
			?>
			<script>
				if( 'undefined' != typeof localStorage ){
					var skin = localStorage.getItem('aadvana-backend-skin');
					if( skin == 'dark' ){

						var element = document.getElementsByTagName("html")[0];
						element.classList.add("aadvana-darkskin");
					}
				}
			</script>
			<div class="wrap wfe-wrapper">
				<h1 class="wp-heading-inline"><?php \esc_html_e( 'File Editor', '0-day-analytics' ); ?></h1>
				<div class="wfe-container">
					<div class="wfe-sidebar" data-collapsed="false">
						<button type="button" class="wfe-sidebar-toggle" aria-expanded="true" aria-controls="wfe-sidebar-inner"><?php \esc_html_e( 'Collapse sidebar', '0-day-analytics' ); ?></button>
						<div id="wfe-sidebar-inner" class="wfe-sidebar-inner">
							<div class="wfe-toolbar">
								<input type="text" id="wfe-search" placeholder="<?php \esc_html_e( '🔍 Search...', '0-day-analytics' ); ?>" />
								<button id="wfe-new-file" class="button"><?php \esc_html_e( '+ File', '0-day-analytics' ); ?></button>
								<button id="wfe-new-folder" class="button"><?php \esc_html_e( '+ Folder', '0-day-analytics' ); ?></button>
							<button id="wfe-upload-file" class="button button-primary"><?php \esc_html_e( '⬆️ Upload', '0-day-analytics' ); ?></button>
							<button id="wfe-empty-trash" class="button button-danger"><?php \esc_html_e( '🧹 Empty Trash', '0-day-analytics' ); ?></button>
							<input type="file" id="wfe-file-input" style="display: none;" multiple />
							</div>
							<div class="wfe-dir-wrapper">
								<div id="wfe-tree" class="wfe-tree">
									<div id="wfe-drop-zone" class="wfe-drop-zone">
										<p><?php \esc_html_e( '📁 Drop files here to upload', '0-day-analytics' ); ?></p>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="wfe-resizer" role="separator" aria-label="Resize sidebar" aria-orientation="vertical" tabindex="0"></div>

					<div class="wfe-editor-area">
						<div id="wfe-filename" class="wfe-filename"><?php \esc_html_e( 'No file selected', '0-day-analytics' ); ?></div>
						<textarea id="wfe-editor"></textarea>
						<div id="wfe-diff" class="wfe-diff"></div>
						<div class="wfe-buttons">
							<button id="wfe-show-diff" class="button"><?php \esc_html_e( 'Preview Diff', '0-day-analytics' ); ?></button>
							<button id="wfe-save" class="button button-primary"><?php \esc_html_e( '💾 Save', '0-day-analytics' ); ?></button>
							<button id="wfe-delete" class="button button-danger"><?php \esc_html_e( '🗑️ Delete', '0-day-analytics' ); ?></button>
							<button id="wfe-undo" class="button"><?php \esc_html_e( '♻ Undo Delete', '0-day-analytics' ); ?></button>
							<button id="wfe-list-backups" class="button"><?php \esc_html_e( '🕒 Backups', '0-day-analytics' ); ?></button>
						</div>
						<div id="wfe-backups" class="wfe-backups"></div>
					</div>
				</div>
			</div>
			
			<!-- Upload/Archive Progress Bar -->
			<div id="wfe-upload-progress" class="wfe-upload-progress" style="display: none;">
				<div class="wfe-upload-progress-content">
					<h3 id="wfe-progress-title"><?php \esc_html_e( 'Uploading Files', '0-day-analytics' ); ?></h3>
					<div class="wfe-progress-bar-container">
						<div id="wfe-progress-bar" class="wfe-progress-bar"></div>
					</div>
					<div id="wfe-progress-text" class="wfe-progress-text">0 / 0</div>
					<div id="wfe-progress-status" class="wfe-progress-status"></div>
					<button id="wfe-progress-cancel" class="button" style="display: none; margin-top: 10px;"><?php \esc_html_e( '✖ Cancel', '0-day-analytics' ); ?></button>
				</div>
			</div>
			<?php
		}

		/**
		 * Safely resolves a path
		 *
		 * @param string $path The input path.
		 *
		 * @return string|false The real path or false if invalid.
		 *
		 * @since 4.0.0
		 */
		private static function safe_path( $path ) {
			$real = \realpath( $path );
			if ( false === $real ) {
				return false;
			}

			$real_norm     = \wp_normalize_path( $real );
			$allowed_roots = array(
				\wp_normalize_path( self::BASE_DIR ),
				\wp_normalize_path( \WP_CONTENT_DIR ),
				\wp_normalize_path( \WP_PLUGIN_DIR ),
				\wp_normalize_path( \get_theme_root() ),
				\wp_normalize_path( self::get_upload_dir()['basedir'] ),
			);

			foreach ( $allowed_roots as $root ) {
				if ( \is_link( $root ) ) {
					$root = \realpath( $root );
				}
				$root = rtrim( $root, '/' );
				if ( 0 === strpos( $real_norm, \trailingslashit( $root ) ) || $real_norm === $root ) {
					return $real;
				}
			}

			return false;
		}

		/**
		 * Loads the meta information
		 *
		 * @return void
		 *
		 * @since 4.0.0
		 */
		private static function load_meta() {
			if ( file_exists( self::get_meta_file() ) ) {
				$data               = json_decode( file_get_contents( self::get_meta_file() ), true );
				self::$last_deleted = ( is_array( $data ) && isset( $data['src'], $data['trash'] ) ) ? $data : null;
			}
		}

		/**
		 * Saves the meta information
		 *
		 * @return void
		 *
		 * @since 4.0.0
		 */
		private static function save_meta() {
			if ( self::get_last_deleted() ) {
				file_put_contents( self::get_meta_file(), json_encode( self::$last_deleted, JSON_PRETTY_PRINT ) );
			} else {
				@unlink( self::get_meta_file() );
			}
		}

		/**
		 * Creates a backup of a file
		 *
		 * @param string $file The file path.
		 *
		 * @return void
		 *
		 * @since 4.0.0
		 */
		private static function create_backup( $file ) {
			$real = self::safe_path( $file );
			if ( ! $real || ! is_file( $real ) ) {
				return;
			}

			$rel = ltrim( str_replace( self::BASE_DIR, '', $real ), \DIRECTORY_SEPARATOR );
			$dir = self::get_backup_dir() . \DIRECTORY_SEPARATOR . dirname( $rel );
			@wp_mkdir_p( $dir );

			$timestamp   = gmdate( 'Ymd-His' );
			$backup_path = $dir . \DIRECTORY_SEPARATOR . basename( $file ) . '.' . $timestamp . '.bak';
			copy( $real, $backup_path );

			// Keep only last N backups.
			$pattern = $dir . \DIRECTORY_SEPARATOR . basename( $file ) . '.*.bak';
			$backups = glob( $pattern );
			rsort( $backups );
			foreach ( array_slice( $backups, self::$max_backups ) as $old ) {
				@unlink( $old );
			}
		}

		/**
		 * Deletes a directory recursively
		 *
		 * @param string $dir The directory path.
		 *
		 * @return void
		 *
		 * @since 4.0.0
		 */
		private static function delete_dir( $dir ) {
			foreach ( scandir( $dir ) as $item ) {
				if ( '.' === $item || '..' === $item ) {
					continue;
				}
				$path = $dir . \DIRECTORY_SEPARATOR . $item;
				is_dir( $path ) ? self::delete_dir( $path ) : @unlink( $path );
			}
			@rmdir( $dir );
		}

		/**
		 * AJAX: Uploads a file
		 *
		 * @return void
		 *
		 * @since 4.0.0
		 */
		public static function ajax_upload_file() {
			// Standardize nonce and capability checks.
			WP_Helper::verify_admin_nonce( 'advan_file_editor_nonce', '_ajax_nonce' );

			if ( empty( $_FILES['file'] ) ) {
				\wp_send_json_error( 'No file uploaded.' );
			}

			$dir    = \sanitize_text_field( $_POST['dir'] ?? self::BASE_DIR );
			$parent = self::safe_path( $dir );
			if ( ! $parent || ! is_dir( $parent ) ) {
				\wp_send_json_error( 'Invalid directory.' );
			}

			$file  = $_FILES['file'];
			$error = $file['error'];

			if ( UPLOAD_ERR_OK !== $error ) {
				\wp_send_json_error( 'Upload error: ' . $error );
			}

			$filename    = \sanitize_file_name( $file['name'] );
			$target_path = $parent . DIRECTORY_SEPARATOR . $filename;

			// Check if file already exists.
			if ( file_exists( $target_path ) ) {
				\wp_send_json_error( 'File already exists: ' . $filename );
			}

			// Move uploaded file to target directory.
			if ( ! move_uploaded_file( $file['tmp_name'], $target_path ) ) {
				\wp_send_json_error( 'Failed to save uploaded file.' );
			}

			\wp_send_json_success(
				array(
					'message' => 'File uploaded successfully.',
					'path'    => $target_path,
					'name'    => $filename,
				)
			);
		}

		/**
		 * AJAX: Creates a new file or folder
		 *
		 * @return void
		 *
		 * @since 4.0.0
		 */
		public static function ajax_create() {
			// Standardize nonce and capability checks.
			WP_Helper::verify_admin_nonce( 'advan_file_editor_nonce', '_ajax_nonce' );

			$dir  = \sanitize_text_field( $_POST['dir'] ?? self::BASE_DIR );
			$name = \sanitize_file_name( $_POST['name'] ?? '' );
			$type = $_POST['type'] ?? 'file';

			$parent = self::safe_path( $dir );
			if ( ! $parent || ! is_dir( $parent ) ) {
				\wp_send_json_error( 'Invalid directory.' );
			}

			$path = $parent . DIRECTORY_SEPARATOR . $name;
			if ( file_exists( $path ) ) {
				\wp_send_json_error( 'Already exists.' );
			}

			if ( 'folder' === $type ) {
				mkdir( $path, 0755 );
			} else {
				file_put_contents( $path, '' );
			}

			\wp_send_json_success( 'Created.' );
		}

		/**
		 * AJAX: Lists directory contents
		 *
		 * @return void
		 *
		 * @since 4.0.0
		 */
		public static function ajax_list_dir() {
			WP_Helper::verify_admin_nonce( 'advan_file_editor_nonce', '_ajax_nonce' );

			$dir  = \sanitize_text_field( $_POST['dir'] ?? \ABSPATH );
			$real = self::safe_path( $dir );
			if ( ! $real || ! is_dir( $real ) ) {
				\wp_send_json_error( 'Invalid directory' );
			}
			$items = array();
			foreach ( scandir( $real ) as $file ) {
				if ( '.' === $file || '..' === $file ) {
					continue;
				}
				$path    = $real . \DIRECTORY_SEPARATOR . $file;
				$type    = is_dir( $path ) ? 'dir' : 'file';
				$display = $file;
				if ( 'file' === $type ) {
					$size = @filesize( $path );
					if ( false !== $size ) {
						$display .= ' (' . \size_format( (int) $size ) . ')';
					}
				}
				$items[] = array(
					'name' => $display,
					'path' => $path,
					'type' => $type,
				);
			}
			\wp_send_json_success( $items );
		}

		/**
		 * AJAX: Gets file content
		 *
		 * @return void
		 *
		 * @since 4.0.0
		 */
		public static function ajax_get_file() {
			WP_Helper::verify_admin_nonce( 'advan_file_editor_nonce', '_ajax_nonce' );

			$file = \sanitize_text_field( $_POST['file'] ?? '' );
			$real = self::safe_path( $file );
			if ( ! $real || ! is_readable( $real ) ) {
				\wp_send_json_error( 'Cannot read file.' );
			}
			\wp_send_json_success( array( 'content' => file_get_contents( $real ) ) );
		}

		/**
		 * AJAX: Saves file content
		 *
		 * @return void
		 *
		 * @since 4.0.0
		 */
		public static function ajax_save_file() {
			WP_Helper::verify_admin_nonce( 'advan_file_editor_nonce', '_ajax_nonce' );

			$file    = \sanitize_text_field( $_POST['file'] ?? '' );
			$content = $_POST['content'] ?? '';
			$real    = self::safe_path( $file );
			if ( ! $real || ! is_writable( $real ) ) {
				\wp_send_json_error( 'Cannot write file.' );
			}

			self::create_backup( $real );
			file_put_contents( $real, \wp_unslash( $content ), LOCK_EX );
			\wp_send_json_success( 'File saved and backup created.' );
		}

		/**
		 * AJAX: Generates a diff preview
		 *
		 * @return void
		 *
		 * @since 4.0.0
		 */
		public static function ajax_diff() {
			WP_Helper::verify_admin_nonce( 'advan_file_editor_nonce', '_ajax_nonce' );

			$file = \sanitize_text_field( $_POST['file'] ?? '' );
			$new  = $_POST['new_content'] ?? '';
			$real = self::safe_path( $file );
			if ( ! $real || ! is_file( $real ) ) {
				\wp_send_json_error( 'Invalid file.' );
			}
			$diff = \wp_text_diff( file_get_contents( $real ), \wp_unslash( $new ), array( 'title' => 'Changes' ) );
			\wp_send_json_success( array( 'diff' => $diff ) );
		}

		/**
		 * AJAX: Deletes a file (moves to trash)
		 *
		 * @return void
		 *
		 * @since 4.0.0
		 */
		public static function ajax_delete() {
			WP_Helper::verify_admin_nonce( 'advan_file_editor_nonce', '_ajax_nonce' );

			$target = \sanitize_text_field( $_POST['path'] ?? '' );
			$real   = self::safe_path( $target );
			if ( ! $real || ! file_exists( $real ) ) {
				\wp_send_json_error( 'Not found.' );
			}

			// Check permissions
			if ( ! is_writable( dirname( $real ) ) ) {
				\wp_send_json_error( 'Permission denied: parent directory not writable.' );
			}

			// Ensure trash directory exists and is writable.
			$trash_dir = self::get_trash_dir();
			if ( ! is_dir( $trash_dir ) ) {
				@\wp_mkdir_p( $trash_dir );
				self::harden_directory( $trash_dir );
			}

			if ( ! is_writable( $trash_dir ) ) {
				\wp_send_json_error( 'Trash directory is not writable.' );
			}

			// Generate unique trash path.
			$base_name  = basename( $real );
			$trash_path = $trash_dir . \DIRECTORY_SEPARATOR . $base_name . '.' . time();
			$counter    = 0;
			while ( file_exists( $trash_path ) ) {
				++$counter;
				$trash_path = $trash_dir . \DIRECTORY_SEPARATOR . $base_name . '.' . time() . '_' . $counter;
			}

			// Try to move to trash.
			if ( @rename( $real, $trash_path ) ) {
				self::$last_deleted = array(
					'src'   => $real,
					'trash' => $trash_path,
				);
				self::save_meta();
				$msg = is_dir( $trash_path ) ? 'Directory moved to trash.' : 'Moved to trash.';
				\wp_send_json_success( $msg );
			} else {
				// If rename fails, try alternative approach for directories.
				if ( is_dir( $real ) ) {
					// Try to copy recursively and then delete.
					if ( self::recursive_copy( $real, $trash_path ) ) {
						self::delete_dir( $real );
						if ( ! file_exists( $real ) ) {
							self::$last_deleted = array(
								'src'   => $real,
								'trash' => $trash_path,
							);
							self::save_meta();
							\wp_send_json_success( 'Directory moved to trash.' );
						} else {
							// Cleanup trash if original still exists
							self::delete_dir( $trash_path );
							\wp_send_json_error( 'Unable to delete directory after copying.' );
						}
					} else {
						\wp_send_json_error( 'Unable to copy directory to trash.' );
					}
				} else {
					\wp_send_json_error( 'Unable to move to trash. Check file permissions.' );
				}
			}
			$src   = self::$last_deleted['src'];
			$trash = self::$last_deleted['trash'];
			\wp_mkdir_p( dirname( $src ) );
			rename( $trash, $src );
			self::$last_deleted = null;
			self::save_meta();
			\wp_send_json_success( array( 'msg' => 'Restored ' . basename( $src ) ) );
		}

		/**
		 * AJAX: Empties the trash
		 *
		 * @return void
		 *
		 * @since 4.0.0
		 */
		public static function ajax_empty_trash() {
			WP_Helper::verify_admin_nonce( 'advan_file_editor_nonce', '_ajax_nonce' );

			self::delete_dir( self::get_trash_dir() );
			\wp_mkdir_p( self::get_trash_dir() );
			self::$last_deleted = null;
			self::save_meta();
			\wp_send_json_success( 'Trash emptied.' );
		}

		/**
		 * AJAX: Lists backups for a file
		 *
		 * @return void
		 *
		 * @since 4.0.0
		 */
		public static function ajax_list_backups() {
			WP_Helper::verify_admin_nonce( 'advan_file_editor_nonce', '_ajax_nonce' );

			$file = \sanitize_text_field( $_POST['file'] ?? '' );
			$real = self::safe_path( $file );
			if ( ! $real ) {
				\wp_send_json_error( 'Invalid path.' );
			}
			$rel     = ltrim( str_replace( self::BASE_DIR, '', $real ), \DIRECTORY_SEPARATOR );
			$dir     = self::get_backup_dir() . \DIRECTORY_SEPARATOR . dirname( $rel );
			$pattern = $dir . \DIRECTORY_SEPARATOR . basename( $real ) . '.*.bak';
			$backups = glob( $pattern );
			rsort( $backups );
			$list = array_map( 'basename', $backups );
			\wp_send_json_success( $list );
		}

		/**
		 * AJAX: Restores a backup
		 *
		 * @return void
		 *
		 * @since 4.0.0
		 */
		public static function ajax_restore_backup() {
			WP_Helper::verify_admin_nonce( 'advan_file_editor_nonce', '_ajax_nonce' );

			$file   = \sanitize_text_field( $_POST['file'] ?? '' );
			$backup = \sanitize_text_field( $_POST['backup'] ?? '' );
			$real   = self::safe_path( $file );
			if ( ! $real || ! is_writable( $real ) ) {
				\wp_send_json_error( 'Invalid file.' );
			}
			$rel         = ltrim( str_replace( self::BASE_DIR, '', $real ), \DIRECTORY_SEPARATOR );
			$dir         = self::get_backup_dir() . \DIRECTORY_SEPARATOR . dirname( $rel );
			$backup_path = $dir . \DIRECTORY_SEPARATOR . basename( $backup );
			if ( ! file_exists( $backup_path ) ) {
				\wp_send_json_error( 'Backup not found.' );
			}
			self::create_backup( $real );
			copy( $backup_path, $real );
			\wp_send_json_success( 'Backup restored.' );
		}

		/**
		 * AJAX: Restores a backup
		 *
		 * @return void
		 *
		 * @since 4.1.0
		 */
		public static function ajax_delete_backup() {
			WP_Helper::verify_admin_nonce( 'advan_file_editor_nonce', '_ajax_nonce' );

			$file = \sanitize_text_field( $_GET['file'] ?? '' );
			$real = self::safe_path( $file );
			if ( ! $real ) {
				\wp_die( 'Invalid file.' );
			}
			$rel         = ltrim( str_replace( self::BASE_DIR, '', $real ), \DIRECTORY_SEPARATOR );
			$backup      = \sanitize_text_field( $_POST['backup'] ?? '' );
			$dir         = self::get_backup_dir() . \DIRECTORY_SEPARATOR . dirname( $rel );
			$backup_path = $dir . \DIRECTORY_SEPARATOR . basename( $backup );
			if ( ! file_exists( $backup_path ) ) {
				\wp_send_json_error( 'Backup not found.' );
			}
			\unlink( $backup_path );
			\wp_send_json_success( 'Backup deleted.' );
		}

		/**
		 * AJAX: Downloads a backup
		 *
		 * @return void
		 *
		 * @since 4.0.0
		 */
		public static function ajax_download_backup() {
			WP_Helper::verify_admin_nonce( 'advan_file_editor_nonce', '_ajax_nonce' );

			$file   = \sanitize_text_field( $_GET['file'] ?? '' );
			$backup = \sanitize_text_field( $_GET['backup'] ?? '' );
			$real   = self::safe_path( $file );
			if ( ! $real ) {
				\wp_die( 'Invalid file.' );
			}
			$rel  = ltrim( str_replace( self::BASE_DIR, '', $real ), \DIRECTORY_SEPARATOR );
			$path = self::get_backup_dir() . \DIRECTORY_SEPARATOR . dirname( $rel ) . \DIRECTORY_SEPARATOR . basename( $backup );
			if ( ! file_exists( $path ) ) {
				\wp_die( 'Backup not found.' );
			}
			header( 'Content-Type: application/octet-stream' );
			header( 'X-Content-Type-Options: nosniff' );
			header( 'Content-Disposition: attachment; filename="' . basename( $backup ) . '"' );
			header( 'Content-Length: ' . filesize( $path ) );
			header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
			header( 'Pragma: no-cache' );
			readfile( $path );
			exit;
		}

		/**
		 * AJAX: Downloads a backup
		 *
		 * @return void
		 *
		 * @since 4.0.0
		 */
		public static function ajax_compare_backup() {
			WP_Helper::verify_admin_nonce( 'advan_file_editor_nonce', '_ajax_nonce' );

			$file   = \sanitize_text_field( $_POST['file'] ?? '' );
			$backup = \sanitize_text_field( $_POST['backup'] ?? '' );
			$real   = self::safe_path( $file );
			if ( ! $real || ! is_file( $real ) ) {
				\wp_send_json_error( 'Invalid file.' );
			}

			$rel         = ltrim( str_replace( self::BASE_DIR, '', $real ), \DIRECTORY_SEPARATOR );
			$backup_path = self::get_backup_dir() . \DIRECTORY_SEPARATOR . dirname( $rel ) . \DIRECTORY_SEPARATOR . basename( $backup );
			if ( ! file_exists( $backup_path ) ) {
				\wp_send_json_error( 'Backup not found.' );
			}

			$old  = file_get_contents( $backup_path );
			$new  = file_get_contents( $real );
			$diff = \wp_text_diff( $old, $new, array( 'title' => 'Backup vs Current' ) );
			\wp_send_json_success( array( 'diff' => $diff ) );
		}

		/**
		 * AJAX: Downloads a regular file (not backup)
		 *
		 * @return void
		 *
		 * @since 4.1.1
		 */
		public static function ajax_download_file() {
			WP_Helper::verify_admin_nonce( 'advan_file_editor_nonce', '_ajax_nonce' );

			$file = \sanitize_text_field( $_GET['file'] ?? '' );
			$real = self::safe_path( $file );
			if ( ! $real || ! is_file( $real ) || ! is_readable( $real ) ) {
				\wp_die( 'Invalid file.' );
			}

			$filename = basename( $real );
			header( 'Content-Type: application/octet-stream' );
			header( 'X-Content-Type-Options: nosniff' );
			header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
			header( 'Content-Length: ' . filesize( $real ) );
			header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
			header( 'Pragma: no-cache' );
			readfile( $real );
			exit;
		}

		/**
		 * AJAX: Renames a file or directory
		 *
		 * @return void
		 *
		 * @since 4.1.1
		 */
		public static function ajax_rename() {
			WP_Helper::verify_admin_nonce( 'advan_file_editor_nonce', '_ajax_nonce' );

			$src      = \sanitize_text_field( $_POST['file'] ?? '' );
			$new_name = \sanitize_file_name( $_POST['new_name'] ?? '' );

			if ( empty( $src ) || empty( $new_name ) ) {
				\wp_send_json_error( 'Missing parameters.' );
			}

			$real = self::safe_path( $src );
			if ( ! $real || ! file_exists( $real ) ) {
				\wp_send_json_error( 'Source not found.' );
			}

			$target = dirname( $real ) . \DIRECTORY_SEPARATOR . $new_name;
			if ( file_exists( $target ) ) {
				\wp_send_json_error( 'Target exists.' );
			}

			if ( ! @rename( $real, $target ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				\wp_send_json_error( 'Unable to rename.' );
			}

			\wp_send_json_success(
				array(
					'new_path' => $target,
					'new_name' => basename( $target ),
				)
			);
		}

		/**
		 * AJAX: Duplicates a file or directory
		 *
		 * @return void
		 *
		 * @since 4.1.1
		 */
		public static function ajax_duplicate() {
			WP_Helper::verify_admin_nonce( 'advan_file_editor_nonce', '_ajax_nonce' );

			$src = \sanitize_text_field( $_POST['file'] ?? '' );
			if ( empty( $src ) ) {
				\wp_send_json_error( 'Missing parameters.' );
			}

			$real = self::safe_path( $src );
			if ( ! $real || ! file_exists( $real ) ) {
				\wp_send_json_error( 'Source not found.' );
			}

			$is_dir   = is_dir( $real );
			$dirname  = dirname( $real );
			$basename = basename( $real );

			// Determine target name.
			$ext       = '';
			$name_only = $basename;
			if ( ! $is_dir && false !== strpos( $basename, '.' ) ) {
				$parts     = explode( '.', $basename );
				$ext       = array_pop( $parts );
				$name_only = implode( '.', $parts );
			}

			$base_candidate = $name_only . '-copy';
			$counter        = 1;
			$target         = '';
			while ( true ) {
				$suffix      = ( $counter > 1 ) ? '-' . $counter : '';
				$target_name = $base_candidate . $suffix . ( $is_dir ? '' : ( ( '' !== $ext ) ? '.' . $ext : '' ) );
				$target      = $dirname . \DIRECTORY_SEPARATOR . $target_name;
				if ( ! file_exists( $target ) ) {
					break;
				}
				++$counter;
			}

			// Perform copy.
			if ( $is_dir ) {
				self::recursive_copy( $real, $target );
			} elseif ( ! @copy( $real, $target ) ) {
				// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
					\wp_send_json_error( 'Unable to duplicate file.' );
			}

			\wp_send_json_success(
				array(
					'new_path' => $target,
					'new_name' => basename( $target ),
				)
			);
		}

		/**
		 * Recursively copies a directory.
		 *
		 * @param string $src Source dir.
		 * @param string $dst Destination dir.
		 *
		 * @return bool True on success, false on failure.
		 *
		 * @since 4.1.1
		 */
		private static function recursive_copy( $src, $dst ) {
			if ( ! is_dir( $src ) ) {
				return false;
			}
			if ( ! @wp_mkdir_p( $dst ) ) {
				return false;
			}
			$dir_handle = opendir( $src );
			if ( false === $dir_handle ) {
				return false;
			}
			$success = true;
			while ( false !== ( $item = readdir( $dir_handle ) ) ) {
				if ( '.' === $item || '..' === $item ) {
					continue;
				}
				$from = $src . \DIRECTORY_SEPARATOR . $item;
				$to   = $dst . \DIRECTORY_SEPARATOR . $item;
				if ( is_dir( $from ) ) {
					if ( ! self::recursive_copy( $from, $to ) ) {
						$success = false;
					}
				} elseif ( ! @copy( $from, $to ) ) {
					// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
						$success = false;
				}
			}
			closedir( $dir_handle );
			return $success;
		}

		/**
		 * Determines whether to use ZipArchive or PclZip
		 *
		 * @return string 'ziparchive' or 'pclzip'
		 *
		 * @since 4.7.2
		 */
		private static function get_zip_method() {
			if ( class_exists( '\ZipArchive', false ) ) {
				return 'ziparchive';
			}
			return 'pclzip';
		}

		/**
		 * Recursively lists all files in a directory
		 *
		 * @param string $dir Directory path.
		 * @param string $base_path Base path for relative paths.
		 *
		 * @return array List of file paths relative to base.
		 *
		 * @since 4.7.1
		 */
		private static function list_files_recursive( $dir, $base_path ) {
			$files      = array();
			$dir_handle = opendir( $dir );
			if ( false === $dir_handle ) {
				return $files;
			}
			while ( false !== ( $item = readdir( $dir_handle ) ) ) {
				if ( '.' === $item || '..' === $item ) {
					continue;
				}
				$path = $dir . \DIRECTORY_SEPARATOR . $item;
				if ( is_dir( $path ) ) {
					$files = array_merge( $files, self::list_files_recursive( $path, $base_path ) );
				} else {
					$files[] = str_replace( $base_path . \DIRECTORY_SEPARATOR, '', $path );
				}
			}
			closedir( $dir_handle );
			return $files;
		}

		/**
		 * AJAX: Starts archive creation (initialization phase)
		 *
		 * @return void
		 *
		 * @since 4.7.1
		 */
		public static function ajax_create_archive_start() {
			WP_Helper::verify_admin_nonce( 'advan_file_editor_nonce', '_ajax_nonce' );

			$source = \sanitize_text_field( $_POST['path'] ?? '' );
			if ( empty( $source ) ) {
				\wp_send_json_error( 'Missing parameters.' );
			}

			$real = self::safe_path( $source );
			if ( ! $real || ! file_exists( $real ) ) {
				\wp_send_json_error( 'Source not found.' );
			}

			$is_dir = is_dir( $real );

			// Determine archive location.
			if ( $is_dir ) {
				$archive_dir = dirname( $real );
			} else {
				$archive_dir = dirname( $real );
			}

			// Generate archive filename.
			$basename     = basename( $real );
			$archive_base = $basename . '-' . gmdate( 'Ymd-His' );
			$archive_path = $archive_dir . \DIRECTORY_SEPARATOR . $archive_base . '.zip';

			// List all files to archive.
			$files = array();
			if ( $is_dir ) {
				$files = self::list_files_recursive( $real, $real );
			} else {
				$files = array( basename( $real ) );
			}

			if ( empty( $files ) ) {
				\wp_send_json_error( 'No files to archive.' );
			}

			\wp_send_json_success(
				array(
					'source'       => $real,
					'archive_path' => $archive_path,
					'files'        => $files,
					'total_files'  => count( $files ),
					'is_dir'       => $is_dir,
				)
			);
		}

		/**
		 * AJAX: Cancels archive creation and deletes partial file
		 *
		 * @return void
		 *
		 * @since 4.7.1
		 */
		public static function ajax_create_archive_cancel() {
			WP_Helper::verify_admin_nonce( 'advan_file_editor_nonce', '_ajax_nonce' );

			$archive_path = \sanitize_text_field( $_POST['archive_path'] ?? '' );
			if ( empty( $archive_path ) ) {
				\wp_send_json_error( 'Missing archive path.' );
			}

			// Validate archive path is within safe directories.
			$archive_real = self::safe_path( dirname( $archive_path ) );
			if ( ! $archive_real ) {
				\wp_send_json_error( 'Invalid archive location.' );
			}

			// Delete the partial archive if it exists.
			if ( file_exists( $archive_path ) ) {
				@unlink( $archive_path );
				// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			}

			\wp_send_json_success( 'Archive creation cancelled.' );
		}

		/**
		 * AJAX: Processes a batch of files for archiving
		 *
		 * @return void
		 *
		 * @since 4.7.1
		 */
		public static function ajax_create_archive_batch() {
			WP_Helper::verify_admin_nonce( 'advan_file_editor_nonce', '_ajax_nonce' );

			$source       = \sanitize_text_field( $_POST['source'] ?? '' );
			$archive_path = \sanitize_text_field( $_POST['archive_path'] ?? '' );
			$files_json   = $_POST['files'] ?? '';
			$processed    = (int) ( $_POST['processed'] ?? 0 );
			$batch_size   = (int) ( $_POST['batch_size'] ?? 50 );
			$is_dir       = 'true' === ( $_POST['is_dir'] ) ? true : false;

			if ( empty( $source ) || empty( $archive_path ) || empty( $files_json ) ) {
				\wp_send_json_error( 'Missing parameters.' );
			}

			$files = json_decode( \stripslashes( $files_json ), true );
			if ( ! is_array( $files ) ) {
				\wp_send_json_error( 'Invalid files list.' );
			}

			$real = self::safe_path( $source );
			if ( ! $real || ! file_exists( $real ) ) {
				\wp_send_json_error( 'Source not found.' );
			}

			// Validate archive path is within safe directories.
			$archive_real = self::safe_path( dirname( $archive_path ) );
			if ( ! $archive_real ) {
				\wp_send_json_error( 'Invalid archive location.' );
			}

			$zip_method = self::get_zip_method();

			if ( 'ziparchive' === $zip_method ) {
				// Use ZipArchive.
				$zip = new \ZipArchive();
				if ( 0 === $processed ) {
					$result = $zip->open( $archive_path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE );
				} else {
					$result = $zip->open( $archive_path );
				}

				if ( true !== $result ) {
					\wp_send_json_error( 'Failed to open/create archive.' );
				}

				// Process batch of files.
				$batch_files = array_slice( $files, $processed, $batch_size );
				foreach ( $batch_files as $file ) {
					if ( $is_dir ) {
						$file_path = $real . \DIRECTORY_SEPARATOR . $file;
					} else {
						$file_path = $real;
					}

					if ( file_exists( $file_path ) && is_file( $file_path ) ) {
						$zip->addFile( $file_path, $file );
					}
				}

				$zip->close();
			} else {
				// Use PclZip.
				require_once \ABSPATH . 'wp-admin/includes/class-pclzip.php';

				$zip = new \PclZip( $archive_path );

				// Process batch of files.
				$batch_files  = array_slice( $files, $processed, $batch_size );
				$files_to_add = array();

				foreach ( $batch_files as $file ) {
					if ( $is_dir ) {
						$file_path = $real . \DIRECTORY_SEPARATOR . $file;
					} else {
						$file_path = $real;
					}

					if ( file_exists( $file_path ) && is_file( $file_path ) ) {
						$files_to_add[] = array(
							\PCLZIP_ATT_FILE_NAME       => $file_path,
							\PCLZIP_ATT_FILE_NEW_SHORT_NAME => $file,
						);
					}
				}

				if ( ! empty( $files_to_add ) ) {
					if ( 0 === $processed ) {
						// First batch - create new archive.
						$result = $zip->create( $files_to_add, \PCLZIP_OPT_REMOVE_ALL_PATH );
					} else {
						// Subsequent batches - add to existing archive.
						$result = $zip->add( $files_to_add, \PCLZIP_OPT_REMOVE_ALL_PATH );
					}

					if ( 0 === $result ) {
						\wp_send_json_error( 'PclZip error: ' . $zip->errorInfo( true ) );
					}
				}
			}

			$new_processed = $processed + count( $batch_files );
			$done          = $new_processed >= count( $files );

			\wp_send_json_success(
				array(
					'processed' => $new_processed,
					'total'     => count( $files ),
					'done'      => $done,
				)
			);
		}

		/**
		 * AJAX: Finalizes archive creation
		 *
		 * @return void
		 *
		 * @since 4.7.1
		 */
		public static function ajax_create_archive_finish() {
			WP_Helper::verify_admin_nonce( 'advan_file_editor_nonce', '_ajax_nonce' );

			$archive_path = \sanitize_text_field( $_POST['archive_path'] ?? '' );
			if ( empty( $archive_path ) ) {
				\wp_send_json_error( 'Missing archive path.' );
			}

			// Validate archive exists.
			if ( ! file_exists( $archive_path ) ) {
				\wp_send_json_error( 'Archive not found.' );
			}

			$size = filesize( $archive_path );
			\wp_send_json_success(
				array(
					'archive' => basename( $archive_path ),
					'size'    => \size_format( $size ),
				)
			);
		}

		/**
		 * AJAX: Starts archive extraction (initialization phase)
		 *
		 * @return void
		 *
		 * @since 4.7.2
		 */
		public static function ajax_extract_archive_start() {
			WP_Helper::verify_admin_nonce( 'advan_file_editor_nonce', '_ajax_nonce' );

			$archive_path = \sanitize_text_field( $_POST['archive_path'] ?? '' );
			$target_dir   = \sanitize_text_field( $_POST['target_dir'] ?? '' );

			if ( empty( $archive_path ) || empty( $target_dir ) ) {
				\wp_send_json_error( 'Missing parameters.' );
			}

			$real_archive = self::safe_path( $archive_path );
			if ( ! $real_archive || ! file_exists( $real_archive ) || ! is_file( $real_archive ) ) {
				\wp_send_json_error( 'Archive not found or invalid.' );
			}

			$real_target = self::safe_path( $target_dir );
			if ( ! $real_target ) {
				\wp_send_json_error( 'Invalid target directory.' );
			}

			$zip_method = self::get_zip_method();

			// Create extraction directory based on archive name.
			$archive_basename = pathinfo( $real_archive, PATHINFO_FILENAME );
			$extract_dir      = $real_target . \DIRECTORY_SEPARATOR . $archive_basename;

			// Check if directory already exists.
			if ( file_exists( $extract_dir ) ) {
				\wp_send_json_error( 'Extraction directory already exists: ' . basename( $extract_dir ) );
			}

			$files = array();

			if ( 'ziparchive' === $zip_method ) {
				// Use ZipArchive.
				$zip = new \ZipArchive();
				$res = $zip->open( $real_archive );

				if ( true !== $res ) {
					\wp_send_json_error( 'Failed to open archive. Error code: ' . $res );
				}

				// Get list of files in archive.
				$total_files = $zip->numFiles;

				for ( $i = 0; $i < $total_files; $i++ ) {
					$stat = $zip->statIndex( $i );
					if ( false !== $stat ) {
						$files[] = array(
							'name'   => $stat['name'],
							'index'  => $i,
							'size'   => $stat['size'],
							'method' => 'ziparchive',
						);
					}
				}

				$zip->close();
			} else {
				// Use PclZip.
				require_once \ABSPATH . 'wp-admin/includes/class-pclzip.php';

				$zip          = new \PclZip( $real_archive );
				$archive_list = $zip->listContent();

				if ( ! is_array( $archive_list ) ) {
					\wp_send_json_error( 'Failed to read archive: ' . $zip->errorInfo( true ) );
				}

				// Get list of files in archive.
				foreach ( $archive_list as $i => $file_info ) {
					$files[] = array(
						'name'   => $file_info['filename'],
						'index'  => $i,
						'size'   => $file_info['size'],
						'method' => 'pclzip',
					);
				}
			}

			if ( empty( $files ) ) {
				\wp_send_json_error( 'Archive is empty or cannot be read.' );
			}

			// Create extraction directory.
			if ( ! @\wp_mkdir_p( $extract_dir ) ) {
				\wp_send_json_error( 'Failed to create extraction directory.' );
			}

			\wp_send_json_success(
				array(
					'archive_path' => $real_archive,
					'extract_dir'  => $extract_dir,
					'files'        => $files,
					'total_files'  => count( $files ),
				)
			);
		}

		/**
		 * AJAX: Processes a batch of files for extraction
		 *
		 * @return void
		 *
		 * @since 4.7.2
		 */
		public static function ajax_extract_archive_batch() {
			WP_Helper::verify_admin_nonce( 'advan_file_editor_nonce', '_ajax_nonce' );

			$archive_path = \sanitize_text_field( $_POST['archive_path'] ?? '' );
			$extract_dir  = \sanitize_text_field( $_POST['extract_dir'] ?? '' );
			$files_json   = $_POST['files'] ?? '';
			$processed    = (int) ( $_POST['processed'] ?? 0 );
			$batch_size   = (int) ( $_POST['batch_size'] ?? 50 );

			if ( empty( $archive_path ) || empty( $extract_dir ) || empty( $files_json ) ) {
				\wp_send_json_error( 'Missing parameters.' );
			}

			$files = json_decode( \stripslashes( $files_json ), true );
			if ( ! is_array( $files ) ) {
				\wp_send_json_error( 'Invalid files list.' );
			}

			$real_archive = self::safe_path( $archive_path );
			if ( ! $real_archive || ! file_exists( $real_archive ) ) {
				\wp_send_json_error( 'Archive not found.' );
			}

			if ( ! file_exists( $extract_dir ) ) {
				\wp_send_json_error( 'Extraction directory not found.' );
			}

		// Process batch of files.
		$batch_files = array_slice( $files, $processed, $batch_size );
		$errors      = array();
		$extracted   = 0;

		// Determine which method was used from the first file.
		$zip_method = ! empty( $batch_files ) && isset( $batch_files[0]['method'] ) ? $batch_files[0]['method'] : 'ziparchive';

		if ( 'ziparchive' === $zip_method ) {
			// Use ZipArchive.
			$zip = new \ZipArchive();
			$res = $zip->open( $real_archive );

			if ( true !== $res ) {
				\wp_send_json_error( 'Failed to open archive for extraction.' );
			}

			foreach ( $batch_files as $file_info ) {
				$file_name = $file_info['name'];
				$index     = $file_info['index'];

				// Skip directories (they'll be created as needed).
				if ( substr( $file_name, -1 ) === '/' ) {
					continue;
				}

				$target_path = $extract_dir . \DIRECTORY_SEPARATOR . $file_name;

				// Ensure target directory exists.
				$target_dir = dirname( $target_path );
				if ( ! file_exists( $target_dir ) ) {
					@\wp_mkdir_p( $target_dir );
				}

				// Extract file.
				$content = $zip->getFromIndex( $index );
				if ( false === $content ) {
					$errors[] = 'Failed to extract: ' . basename( $file_name );
					continue;
				}

				if ( false === @file_put_contents( $target_path, $content ) ) {
					$errors[] = 'Failed to write: ' . basename( $file_name );
					continue;
				}

				++$extracted;
			}

			$zip->close();
		} else {
			// Use PclZip.
			require_once \ABSPATH . 'wp-admin/includes/class-pclzip.php';

			$zip          = new \PclZip( $real_archive );
			$archive_list = $zip->listContent();

			if ( ! is_array( $archive_list ) ) {
				\wp_send_json_error( 'Failed to read archive: ' . $zip->errorInfo( true ) );
			}

			foreach ( $batch_files as $file_info ) {
				$file_name = $file_info['name'];

				// Skip directories.
				if ( substr( $file_name, -1 ) === '/' ) {
					continue;
				}

				$target_path = $extract_dir . \DIRECTORY_SEPARATOR . $file_name;

				// Ensure target directory exists.
				$target_dir = dirname( $target_path );
				if ( ! file_exists( $target_dir ) ) {
					@\wp_mkdir_p( $target_dir );
				}

				// Extract single file.
				$result = $zip->extract(
					\PCLZIP_OPT_BY_NAME,
					$file_name,
					\PCLZIP_OPT_PATH,
					$extract_dir,
					\PCLZIP_OPT_REMOVE_ALL_PATH
				);

				if ( 0 === $result ) {
					$errors[] = 'Failed to extract: ' . basename( $file_name );
					continue;
				}

				// Move file to correct location if needed (PclZip extracts to flat structure with REMOVE_ALL_PATH).
				if ( strpos( $file_name, '/' ) !== false ) {
					$flat_file = $extract_dir . \DIRECTORY_SEPARATOR . basename( $file_name );
					if ( file_exists( $flat_file ) && $flat_file !== $target_path ) {
						if ( ! @rename( $flat_file, $target_path ) ) {
							$errors[] = 'Failed to move: ' . basename( $file_name );
							continue;
						}
					}
				}

				++$extracted;
			}
		}

	$new_processed = $processed + count( $batch_files );
	$done          = $new_processed >= count( $files );

	\wp_send_json_success(
		array(
			'processed' => $new_processed,
			'total'     => count( $files ),
			'done'      => $done,
			'extracted' => $extracted,
			'errors'    => $errors,
		)
	);
}

		/**
		 * AJAX: Cancels archive extraction and deletes partial files
		 *
		 * @return void
		 *
		 * @since 4.7.2
		 */
		public static function ajax_extract_archive_cancel() {
			WP_Helper::verify_admin_nonce( 'advan_file_editor_nonce', '_ajax_nonce' );

			$extract_dir = \sanitize_text_field( $_POST['extract_dir'] ?? '' );

			if ( empty( $extract_dir ) ) {
				\wp_send_json_error( 'Missing extraction directory.' );
			}

			// Delete the partial extraction directory if it exists.
			if ( file_exists( $extract_dir ) && is_dir( $extract_dir ) ) {
				self::delete_dir( $extract_dir );
			}

			\wp_send_json_success( 'Extraction cancelled and partial files removed.' );
		}

		/**
		 * AJAX: Finalizes archive extraction
		 *
		 * @return void
		 *
		 * @since 4.7.2
		 */
		public static function ajax_extract_archive_finish() {
			WP_Helper::verify_admin_nonce( 'advan_file_editor_nonce', '_ajax_nonce' );

			$extract_dir  = \sanitize_text_field( $_POST['extract_dir'] ?? '' );
			$archive_path = \sanitize_text_field( $_POST['archive_path'] ?? '' );
			$delete_after = isset( $_POST['delete_archive'] ) && $_POST['delete_archive'];

			if ( empty( $extract_dir ) ) {
				\wp_send_json_error( 'Missing extraction directory.' );
			}

			// Validate extraction directory exists.
			if ( ! file_exists( $extract_dir ) || ! is_dir( $extract_dir ) ) {
				\wp_send_json_error( 'Extraction directory not found.' );
			}

			// Optionally delete the archive after extraction.
			// if ( $delete_after && ! empty( $archive_path ) && file_exists( $archive_path ) ) {
			// 	@unlink( $archive_path );
			// }

			// Count extracted items.
			$items = self::count_dir_items( $extract_dir );

			\wp_send_json_success(
				array(
					'extract_dir' => basename( $extract_dir ),
					'items'       => $items,
				)
			);
		}

		/**
		 * Counts items in a directory recursively
		 *
		 * @param string $dir Directory path.
		 *
		 * @return int Number of items.
		 *
		 * @since 4.7.2
		 */
		private static function count_dir_items( $dir ) {
			$count = 0;
			if ( ! is_dir( $dir ) ) {
				return $count;
			}

			$dir_handle = opendir( $dir );
			if ( false === $dir_handle ) {
				return $count;
			}

			while ( false !== ( $item = readdir( $dir_handle ) ) ) {
				if ( '.' === $item || '..' === $item ) {
					continue;
				}
				$path = $dir . \DIRECTORY_SEPARATOR . $item;
				if ( is_dir( $path ) ) {
					$count += self::count_dir_items( $path );
				} else {
					++$count;
				}
			}
			closedir( $dir_handle );
			return $count;
		}
	}
}
