<?php
/**
 * Class: File functions helper file.
 *
 * Helper class used for extraction / loading classes.
 *
 * @package advanced-analytics
 *
 * @since 1.1.0
 */

declare(strict_types=1);

namespace ADVAN\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\ADVAN\Helpers\File_Helper' ) ) {
	/**
	 * Responsible for file operations.
	 *
	 * @since 1.1.0
	 */
	class File_Helper {

		/**
		 * Try to initialize and return WP_Filesystem instance.
		 *
		 * @return null|\WP_Filesystem_Base Null when unavailable or failed to initialize.
		 *
		 * @since 4.1.2
		 */
		private static function get_wp_filesystem() {
			global $wp_filesystem;

			// Attempt to bootstrap the WP_Filesystem API.
			if ( ! function_exists( '\\WP_Filesystem' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}

			try {
				// Initialize if not already.
				if ( ! isset( $wp_filesystem ) || ! is_object( $wp_filesystem ) ) {
					\WP_Filesystem();
				}
			} catch ( \Throwable $e ) {
				// Initialization failed, fall back to native PHP.
				return null;
			}

			// Check if the filesystem is properly initialized.
			/**
			 * bug in WordPress core. The WP_Filesystem_FTP and WP_Filesystem_FTPext classes create temporary files during put_contents operations, but if the FTP connection is invalid or null, the method throws an exception without cleaning up the temp file, resulting in orphaned files in tmp.
			 * The fix in the plugin works around this by validating the filesystem instance before use, ensuring fallback to native PHP functions when the WP_Filesystem is broken. This prevents the temp file leakage.
			 */
			if ( isset( $wp_filesystem ) && is_object( $wp_filesystem ) ) {
				// For FTP, ensure the connection is established.
				if ( ( $wp_filesystem instanceof \WP_Filesystem_FTP || $wp_filesystem instanceof \WP_Filesystem_FTPext ) && ( ! isset( $wp_filesystem->ftp ) || ! $wp_filesystem->ftp ) ) {
					return null;
				}
				return $wp_filesystem;
			}

			return null;
		}

		/**
		 * Native PHP file writer with append support and locking.
		 *
		 * @param string $file_path Absolute path to file.
		 * @param string $content   Content to write.
		 * @param bool   $append    Append instead of overwrite.
		 *
		 * @return bool True on success.
		 *
		 * @since 4.1.2
		 */
		private static function native_put_contents( string $file_path, string $content, bool $append = false ): bool {
			$flags = LOCK_EX;
			if ( $append ) {
				$flags |= FILE_APPEND;
			}
			$bytes = @file_put_contents( $file_path, $content, $flags ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			return ( false !== $bytes );
		}

		/**
		 * Normalize a filesystem path in a cross-platform way.
		 *
		 * @param string $path The path to normalize.
		 *
		 * @return string Normalized path using forward slashes.
		 *
		 * @since 4.1.1
		 */
		private static function normalize_path( string $path ): string {
			if ( function_exists( '\\wp_normalize_path' ) ) {
				return \wp_normalize_path( $path );
			}
			return str_replace( '\\', '/', $path );
		}

		/**
		 * Check whether a path is under an allowed base directory.
		 *
		 * NOTE: This is a prefix check on normalized paths. Callers should prefer
		 * realpath() when the target exists, but for non-existent paths this
		 * provides a conservative safeguard against traversal attempts.
		 *
		 * @param string $path Absolute path to check.
		 * @param string $base Allowed base directory.
		 *
		 * @return bool True when $path is within $base.
		 *
		 * @since 4.1.1
		 */
		private static function is_under_base( string $path, string $base ): bool {
			$norm_base = rtrim( self::normalize_path( \trailingslashit( $base ) ), '/' );
			$norm_path = self::normalize_path( $path );
			return ( 0 === strpos( $norm_path, $norm_base . '/' ) ) || ( $norm_path === $norm_base );
		}

		/**
		 * Keeps the string representation of the last error
		 *
		 * @var string|\WP_Error
		 *
		 * @since 1.1.0
		 */
		private static $last_error = '';

		/**
		 * Creates index file in the given directory.
		 *
		 * @param string $path - Path in which index file should be created. If does not exist - the method will try to create it.
		 *
		 * @return boolean
		 *
		 * @since 1.1.0
		 */
		public static function create_index_file( string $path ): bool {
			// Check if directory exists.
			$path = \trailingslashit( $path );

			return self::write_to_file( $path . 'index.php', '<?php /*[' . ADVAN_NAME . ' plugin: This file was auto-generated to prevent directory listing ]*/ exit;' );
		}

		/**
		 * Creates htaccess file in given directory.
		 *
		 * @param string $path - Path in which htaccess file should be created. If does not exist - the method will try to create it.
		 *
		 * @return boolean
		 *
		 * @since 1.1.0
		 */
		public static function create_htaccess_file( string $path ): bool {
			// Ensure trailing slash.
			$path = \trailingslashit( $path );
			// Hardened directives (Apache). Nginx will ignore this but index.php prevents listing.
			$contents = "Require all denied\nDeny from all\n";
			return self::write_to_file( $path . '.htaccess', $contents );
		}

		/**
		 * Writes content to given file
		 *
		 * @param string  $filename - Full path to the file.
		 * @param string  $content - Content to write into the file.
		 * @param boolean $append - Appends the content to the file if it exists.
		 *
		 * @return boolean
		 *
		 * @since 1.1.0
		 */
		public static function write_to_file( string $filename, string $content, bool $append = false ): bool {
			$logging_dir = dirname( $filename );

			// Restrict writes to an allowed base directory (default: WP_CONTENT_DIR).
			$allowed_base = \apply_filters( ADVAN_TEXTDOMAIN . 'fs_base_dir', WP_CONTENT_DIR );
			$target_dir   = self::normalize_path( $logging_dir );
			if ( ! self::is_under_base( $target_dir, (string) $allowed_base ) ) {
				self::$last_error = new \WP_Error( 'path_not_allowed', __( 'Refusing to write outside the allowed base directory.', '0-day-analytics' ) );
				return false;
			}

			// Ensure destination directory exists, try WP first then native.
			if ( ! is_dir( $logging_dir ) ) {
				if ( ! function_exists( '\\wp_mkdir_p' ) ) {
					require_once ABSPATH . 'wp-admin/includes/file.php';
				}
				$made_dir = false;
				try {
					$made_dir = \wp_mkdir_p( $logging_dir );
				} catch ( \Throwable $e ) {
					$made_dir = false;
				}
				if ( ! $made_dir ) {
					$made_dir = @mkdir( $logging_dir, 0755, true ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.WP.AlternativeFunctions.mkdir_directoryPermissions
				}
				if ( ! $made_dir ) {
					self::$last_error = new \WP_Error(
						'mkdir_failed',
						sprintf(
							/* translators: %s: Directory path. */
							__( 'Unable to create directory %s. Is its parent directory writable by the server?', '0-day-analytics' ),
							\esc_html( $logging_dir )
						)
					);
					return false;
				}
			}

			$file_path = $filename;

			// Basic symlink check (avoid writing through symlink).
			if ( is_link( $file_path ) ) {
				self::$last_error = new \WP_Error( 'symlink_write_blocked', __( 'Refusing to write to symlinked path.', '0-day-analytics' ) );
				return false;
			}

			// Try WP_Filesystem first.
			$result = false;
			$fs     = self::get_wp_filesystem();
			if ( $fs ) {
				try {
					if ( $append && $fs->exists( $file_path ) ) {
						$existing_content = $fs->get_contents( $file_path );
						if ( false === $existing_content ) {
							$existing_content = '';
						}
						$result = (bool) $fs->put_contents( $file_path, $existing_content . $content );
					} else {
						$result = (bool) $fs->put_contents( $file_path, $content );
					}
				} catch ( \Throwable $e ) {
					$result = false; // Will fall back below.
				}
			}

			// Fall back to native PHP functions if WP_Filesystem path failed or unavailable.
			if ( false === $result ) {
				$result = self::native_put_contents( $file_path, $content, $append );
			}

			if ( false === $result ) {
				self::$last_error = new \WP_Error(
					'write_failed',
					sprintf(
						/* translators: %s: Directory path. */
						__( 'Trying to write to the file %s failed.', '0-day-analytics' ),
						\esc_html( $file_path )
					)
				);
				return false;
			}

			// Best-effort permission hardening.
			@chmod( $file_path, 0640 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.WP.AlternativeFunctions.file_system_operations_chmod

			return true;
		}

		/**
		 * Getter for the last error variable of the class
		 *
		 * @return \WP_Error|string
		 *
		 * @since 1.1.0
		 */
		public static function get_last_error() {
			return self::$last_error;
		}

		/**
		 * Returns the file size in human readable format.
		 *
		 * @param string $filename - The name of the file (including path) to check the size of.
		 *
		 * @return string
		 *
		 * @since 1.1.0
		 */
		public static function format_file_size( $filename ): string {

			if ( \is_string( $filename ) && \file_exists( $filename ) ) {

				$size = filesize( $filename );

				return \size_format( $size );
			}

			return '0 B';
		}

		/**
		 * Builds and returns download link for log file.
		 *
		 * @return string
		 *
		 * @since 1.1.0
		 */
		public static function download_link(): string {
			$url = \add_query_arg(
				array(
					'action'   => 'advanced_analytics_download_log_file',
					'_wpnonce' => \wp_create_nonce( 'advan-plugin-data' ),
				),
				\admin_url( 'admin-ajax.php' )
			);

			return $url;
		}

		/**
		 * Checks the file and initiates the download
		 *
		 * @param string $file_path - The full path to the file.
		 *
		 * @return void
		 *
		 * @since 1.1.0
		 */
		public static function download( $file_path ) {
			set_time_limit( 0 );
			// Raise memory limit in an allowed WordPress way if possible.
			if ( function_exists( 'wp_raise_memory_limit' ) ) {
				\wp_raise_memory_limit( 'admin' );
			}
			if ( empty( $file_path ) ) {
				echo 'There is no file to download!';
				exit;
			}

			// Resolve and validate path against allowed base directory.
			$allowed_base      = \apply_filters( ADVAN_TEXTDOMAIN . 'download_base_dir', WP_CONTENT_DIR );
			$real_allowed_base = realpath( $allowed_base );
			$real_requested    = realpath( $file_path );
			if ( ! $real_requested || ! $real_allowed_base || strpos( $real_requested, $real_allowed_base ) !== 0 || is_link( $real_requested ) ) {
				echo 'Invalid file path';
				exit;
			}

			if ( ! \file_exists( $real_requested ) ) {
				echo 'File does not exist!';
				exit;
			}

			$file_info     = pathinfo( $real_requested );
			$file_name_raw = $file_info['basename'];
			// Sanitize filename for headers.
			$file_name            = preg_replace( '/[^A-Za-z0-9._\- ]/u', '_', $file_name_raw );
			$file_extension       = isset( $file_info['extension'] ) ? $file_info['extension'] : '';
			$default_content_type = 'application/octet-stream';
			$content_type         = $default_content_type;
			if ( $file_extension && array_key_exists( $file_extension, self::mime_types() ) ) {
				$content_type = self::mime_types()[ $file_extension ];
			}

			$size   = \filesize( $real_requested );
			$offset = 0;
			$length = $size;

			// Support for partial content requests.
			$range_header = isset( $_SERVER['HTTP_RANGE'] ) ? wp_unslash( $_SERVER['HTTP_RANGE'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			if ( $range_header && preg_match( '/bytes=(\d+)-(\d+)?/', $range_header, $matches ) ) {
				$offset = (int) $matches[1];
				if ( isset( $matches[2] ) && '' !== $matches[2] ) { // Yoda condition for coding standards.
					$end    = (int) $matches[2];
					$length = max( 0, min( $size - $offset, $end - $offset + 1 ) );
				} else {
					$length = max( 0, $size - $offset );
				}
				if ( $offset > $size ) {
					header( 'HTTP/1.1 416 Requested Range Not Satisfiable' );
					echo 'Invalid range';
					exit;
				}
				header( 'HTTP/1.1 206 Partial Content' );
				header( 'Content-Range: bytes ' . $offset . '-' . ( $offset + $length - 1 ) . '/' . $size );
			}

			// Standard download headers.
			header( "Content-Disposition: attachment; filename=\"{$file_name}\"; filename*=UTF-8''" . rawurlencode( $file_name ) );
			header( 'Content-Type: ' . $content_type );
			header( 'X-Content-Type-Options: nosniff' );
			header( 'Accept-Ranges: bytes' );
			header( 'Pragma: public' );
			header( 'Expires: 0' );
			header( 'Cache-Control: private, no-store, no-cache, must-revalidate' );
			header( 'Content-Length: ' . ( isset( $_SERVER['HTTP_RANGE'] ) ? $length : $size ) );

			$chunksize = 8 * 1024 * 1024; // 8MB
			$remaining = $length;
			$handle    = fopen( $real_requested, 'rb' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
			if ( ! $handle ) {
				echo 'Cannot open file';
				exit;
			}
			if ( $offset ) {
				fseek( $handle, $offset );
			}

			while ( $remaining > 0 && ! feof( $handle ) && ( connection_status() === CONNECTION_NORMAL ) ) {
				$read_length = ( $remaining > $chunksize ) ? $chunksize : $remaining;
				$buffer      = fread( $handle, $read_length ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fread
				$remaining  -= strlen( $buffer );
				print $buffer; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				ob_flush();
				flush();
			}
			fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

			if ( connection_status() !== CONNECTION_NORMAL ) {
				echo 'Connection aborted';
			}
			exit;
		}

		/**
		 * Function to get correct MIME type for download
		 *
		 * @return array
		 *
		 * @since 1.1.0
		 */
		public static function mime_types(): array {
			/* Just add any required MIME type if you are going to download something not listed here.*/
			$mime_types = array(
				'323'     => 'text/h323',
				'acx'     => 'application/internet-property-stream',
				'ai'      => 'application/postscript',
				'aif'     => 'audio/x-aiff',
				'aifc'    => 'audio/x-aiff',
				'aiff'    => 'audio/x-aiff',
				'asf'     => 'video/x-ms-asf',
				'asr'     => 'video/x-ms-asf',
				'asx'     => 'video/x-ms-asf',
				'au'      => 'audio/basic',
				'avi'     => 'video/x-msvideo',
				'axs'     => 'application/olescript',
				'bas'     => 'text/plain',
				'bcpio'   => 'application/x-bcpio',
				'bin'     => 'application/octet-stream',
				'bmp'     => 'image/bmp',
				'c'       => 'text/plain',
				'cat'     => 'application/vnd.ms-pkiseccat',
				'cdf'     => 'application/x-cdf',
				'cer'     => 'application/x-x509-ca-cert',
				'class'   => 'application/octet-stream',
				'clp'     => 'application/x-msclip',
				'cmx'     => 'image/x-cmx',
				'cod'     => 'image/cis-cod',
				'cpio'    => 'application/x-cpio',
				'crd'     => 'application/x-mscardfile',
				'crl'     => 'application/pkix-crl',
				'crt'     => 'application/x-x509-ca-cert',
				'csh'     => 'application/x-csh',
				'css'     => 'text/css',
				'dcr'     => 'application/x-director',
				'der'     => 'application/x-x509-ca-cert',
				'dir'     => 'application/x-director',
				'dll'     => 'application/x-msdownload',
				'dms'     => 'application/octet-stream',
				'doc'     => 'application/msword',
				'dot'     => 'application/msword',
				'dvi'     => 'application/x-dvi',
				'dxr'     => 'application/x-director',
				'eps'     => 'application/postscript',
				'etx'     => 'text/x-setext',
				'evy'     => 'application/envoy',
				'exe'     => 'application/octet-stream',
				'fif'     => 'application/fractals',
				'flr'     => 'x-world/x-vrml',
				'gif'     => 'image/gif',
				'gtar'    => 'application/x-gtar',
				'gz'      => 'application/x-gzip',
				'h'       => 'text/plain',
				'hdf'     => 'application/x-hdf',
				'hlp'     => 'application/winhlp',
				'hqx'     => 'application/mac-binhex40',
				'hta'     => 'application/hta',
				'htc'     => 'text/x-component',
				'htm'     => 'text/html',
				'html'    => 'text/html',
				'htt'     => 'text/webviewhtml',
				'ico'     => 'image/x-icon',
				'ief'     => 'image/ief',
				'iii'     => 'application/x-iphone',
				'ins'     => 'application/x-internet-signup',
				'isp'     => 'application/x-internet-signup',
				'jfif'    => 'image/pipeg',
				'jpe'     => 'image/jpeg',
				'jpeg'    => 'image/jpeg',
				'jpg'     => 'image/jpeg',
				'js'      => 'application/x-javascript',
				'latex'   => 'application/x-latex',
				'lha'     => 'application/octet-stream',
				'lsf'     => 'video/x-la-asf',
				'lsx'     => 'video/x-la-asf',
				'lzh'     => 'application/octet-stream',
				'm13'     => 'application/x-msmediaview',
				'm14'     => 'application/x-msmediaview',
				'm3u'     => 'audio/x-mpegurl',
				'man'     => 'application/x-troff-man',
				'mdb'     => 'application/x-msaccess',
				'me'      => 'application/x-troff-me',
				'mht'     => 'message/rfc822',
				'mhtml'   => 'message/rfc822',
				'mid'     => 'audio/mid',
				'mny'     => 'application/x-msmoney',
				'mov'     => 'video/quicktime',
				'movie'   => 'video/x-sgi-movie',
				'mp2'     => 'video/mpeg',
				'mp3'     => 'audio/mpeg',
				'mpa'     => 'video/mpeg',
				'mpe'     => 'video/mpeg',
				'mpeg'    => 'video/mpeg',
				'mpg'     => 'video/mpeg',
				'mpp'     => 'application/vnd.ms-project',
				'mpv2'    => 'video/mpeg',
				'ms'      => 'application/x-troff-ms',
				'mvb'     => 'application/x-msmediaview',
				'nws'     => 'message/rfc822',
				'oda'     => 'application/oda',
				'p10'     => 'application/pkcs10',
				'p12'     => 'application/x-pkcs12',
				'p7b'     => 'application/x-pkcs7-certificates',
				'p7c'     => 'application/x-pkcs7-mime',
				'p7m'     => 'application/x-pkcs7-mime',
				'p7r'     => 'application/x-pkcs7-certreqresp',
				'p7s'     => 'application/x-pkcs7-signature',
				'pbm'     => 'image/x-portable-bitmap',
				'pdf'     => 'application/pdf',
				'pfx'     => 'application/x-pkcs12',
				'pgm'     => 'image/x-portable-graymap',
				'pko'     => 'application/ynd.ms-pkipko',
				'pma'     => 'application/x-perfmon',
				'pmc'     => 'application/x-perfmon',
				'pml'     => 'application/x-perfmon',
				'pmr'     => 'application/x-perfmon',
				'pmw'     => 'application/x-perfmon',
				'pnm'     => 'image/x-portable-anymap',
				'pot'     => 'application/vnd.ms-powerpoint',
				'ppm'     => 'image/x-portable-pixmap',
				'pps'     => 'application/vnd.ms-powerpoint',
				'ppt'     => 'application/vnd.ms-powerpoint',
				'prf'     => 'application/pics-rules',
				'ps'      => 'application/postscript',
				'pub'     => 'application/x-mspublisher',
				'qt'      => 'video/quicktime',
				'ra'      => 'audio/x-pn-realaudio',
				'ram'     => 'audio/x-pn-realaudio',
				'ras'     => 'image/x-cmu-raster',
				'rgb'     => 'image/x-rgb',
				'rmi'     => 'audio/mid',
				'roff'    => 'application/x-troff',
				'rtf'     => 'application/rtf',
				'rtx'     => 'text/richtext',
				'scd'     => 'application/x-msschedule',
				'sct'     => 'text/scriptlet',
				'setpay'  => 'application/set-payment-initiation',
				'setreg'  => 'application/set-registration-initiation',
				'sh'      => 'application/x-sh',
				'shar'    => 'application/x-shar',
				'sit'     => 'application/x-stuffit',
				'snd'     => 'audio/basic',
				'spc'     => 'application/x-pkcs7-certificates',
				'spl'     => 'application/futuresplash',
				'src'     => 'application/x-wais-source',
				'sst'     => 'application/vnd.ms-pkicertstore',
				'stl'     => 'application/vnd.ms-pkistl',
				'stm'     => 'text/html',
				'svg'     => 'image/svg+xml',
				'sv4cpio' => 'application/x-sv4cpio',
				'sv4crc'  => 'application/x-sv4crc',
				't'       => 'application/x-troff',
				'tar'     => 'application/x-tar',
				'tcl'     => 'application/x-tcl',
				'tex'     => 'application/x-tex',
				'texi'    => 'application/x-texinfo',
				'texinfo' => 'application/x-texinfo',
				'tgz'     => 'application/x-compressed',
				'tif'     => 'image/tiff',
				'tiff'    => 'image/tiff',
				'tr'      => 'application/x-troff',
				'trm'     => 'application/x-msterminal',
				'tsv'     => 'text/tab-separated-values',
				'txt'     => 'text/plain',
				'uls'     => 'text/iuls',
				'ustar'   => 'application/x-ustar',
				'vcf'     => 'text/x-vcard',
				'vrml'    => 'x-world/x-vrml',
				'wav'     => 'audio/x-wav',
				'wcm'     => 'application/vnd.ms-works',
				'wdb'     => 'application/vnd.ms-works',
				'wks'     => 'application/vnd.ms-works',
				'wmf'     => 'application/x-msmetafile',
				'wps'     => 'application/vnd.ms-works',
				'wri'     => 'application/x-mswrite',
				'wrl'     => 'x-world/x-vrml',
				'wrz'     => 'x-world/x-vrml',
				'xaf'     => 'x-world/x-vrml',
				'xbm'     => 'image/x-xbitmap',
				'xla'     => 'application/vnd.ms-excel',
				'xlc'     => 'application/vnd.ms-excel',
				'xlm'     => 'application/vnd.ms-excel',
				'xls'     => 'application/vnd.ms-excel',
				'xlt'     => 'application/vnd.ms-excel',
				'xlw'     => 'application/vnd.ms-excel',
				'xof'     => 'x-world/x-vrml',
				'xpm'     => 'image/x-xpixmap',
				'xwd'     => 'image/x-xwindowdump',
				'z'       => 'application/x-compress',
				'rar'     => 'application/x-rar-compressed',
				'zip'     => 'application/zip',
			);
			return $mime_types;
		}

		/**
		 * Get full file path to the site's wp-config.php file.
		 *
		 * @since 1.1.0
		 *
		 * @return string Full path to the wp-config.php file or a blank string if modifications for the file are disabled.
		 */
		public static function get_wp_config_file_path() {

			if ( file_exists( ABSPATH . 'wp-config.php' ) ) {

				/** The config file resides in ABSPATH */
				$path = ABSPATH . 'wp-config.php';

			} elseif ( file_exists( dirname( ABSPATH ) . '/wp-config.php' ) && ! file_exists( dirname( ABSPATH ) . '/wp-settings.php' ) ) {

				/** The config file resides one level above ABSPATH */
				$path = dirname( ABSPATH ) . '/wp-config.php';

			} else {
				$path = '';
			}

			/**
			 * Gives the ability to manually change the path to the config file.
			 *
			 * @param string - The current value for WP config file path.
			 *
			 * @since 1.1.0
			 */
			$path = \apply_filters( ADVAN_TEXTDOMAIN . 'config_file_path', (string) $path );

			return $path;
		}

		/**
		 * Just returns randomized string.
		 *
		 * @return string
		 *
		 * @since 1.1.0
		 */
		public static function generate_random_file_name() {
			try {
				return bin2hex( random_bytes( 16 ) );
			} catch ( \Exception $e ) {
				// Fallback if random_bytes not available.
				return wp_generate_password( 32, false );
			}
		}

		/**
		 * Checks if given file is valid PHP file.
		 *
		 * @param string $file_name - The name of the file (including path) to check the size of.
		 *
		 * @return boolean
		 *
		 * @since 1.8.2
		 */
		public static function is_file_valid_php( string $file_name ): bool {
			if ( ! file_exists( $file_name ) ) {
				return false;
			}
			$allowed_types      = array( 'php' );
			$allowed_mime_types = array( 'text/x-php', 'application/x-httpd-php', 'application/php', 'application/x-php', 'text/php', 'text/plain' );
			$finfo              = @finfo_open( FILEINFO_MIME_TYPE ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			$mime_type          = $finfo ? @finfo_file( $finfo, $file_name ) : false; // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			if ( $finfo ) {
				finfo_close( $finfo );
			}
			$extension = strtolower( pathinfo( $file_name, PATHINFO_EXTENSION ) );
			if ( ! in_array( $extension, $allowed_types, true ) ) {
				return false;
			}
			if ( empty( $mime_type ) || ! in_array( $mime_type, $allowed_mime_types, true ) ) {
				return false;
			}
			return true;
		}

		/**
		 * Checks if the file is writable.
		 *
		 * @param string $file_path - The full path to the file.
		 *
		 * @return boolean
		 *
		 * @since 1.9.2
		 */
		public static function is_writable( string $file_path ): bool {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			return (bool) \wp_is_writable( $file_path );
		}

		/**
		 * Removes empty lines from file using as less memory as possible.
		 *
		 * @param string $file_path - The file (absolute) to remove empty lines from.
		 *
		 * @return bool
		 *
		 * @since 1.9.2
		 */
		public static function remove_empty_lines_low_memory( string $file_path ): bool {
			// Ensure path is allowed and not a symlink.
			$allowed_base = \apply_filters( ADVAN_TEXTDOMAIN . 'fs_base_dir', WP_CONTENT_DIR );
			if ( is_link( $file_path ) || ! self::is_under_base( self::normalize_path( $file_path ), (string) $allowed_base ) ) {
				return false;
			}

			// Open the input file and a temporary output file.
			$in = fopen( $file_path, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
			if ( ! $in ) {
				return false;
			}

			require_once ABSPATH . 'wp-admin/includes/file.php';
			$temp_path = \wp_tempnam( $file_path );
			$out       = fopen( $temp_path, 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
			if ( ! $out ) {
				fclose( $in ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
				return false;
			}

			// Process file line by line.
			$line = fgets( $in ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fgets
			while ( false !== $line ) {
				if ( trim( $line ) !== '' ) {
					fwrite( $out, $line ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
				}
				$line = fgets( $in ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fgets
			}

			fclose( $in ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
			fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

			// Replace the original file with the filtered one using WP_Filesystem.
			global $wp_filesystem;
			\WP_Filesystem();
			if ( isset( $wp_filesystem ) && is_object( $wp_filesystem ) ) {
				$move = false;
				try {
					$move = $wp_filesystem->move( $temp_path, $file_path, true );
				} catch ( \Throwable $e ) {
					$move = false;
				}
				if ( ! $move ) {
					// Try native rename as a fallback when WP_Filesystem move fails.
					$move = @rename( $temp_path, $file_path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.WP.AlternativeFunctions.rename_rename
				}
				if ( ! $move ) {
					// Cleanup temp file on failure.
					if ( isset( $wp_filesystem ) && is_object( $wp_filesystem ) ) {
						@$wp_filesystem->delete( $temp_path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
					} else {
						@unlink( $temp_path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.WP.AlternativeFunctions.unlink_unlink
					}
				}
				return (bool) $move;
			}

			// Fallback if filesystem is unavailable.
			return @rename( $temp_path, $file_path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.WP.AlternativeFunctions.rename_rename
		}
	}
}
