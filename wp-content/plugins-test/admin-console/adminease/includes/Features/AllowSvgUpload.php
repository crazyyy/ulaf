<?php
namespace AdminEase\Features;

use AdminEase\Plugin;
use enshrined\svgSanitize\Sanitizer;

defined( 'ABSPATH' ) || exit;

/**
 * Allow SVG Upload Feature
 * Enables SVG file uploads to WordPress media library with professional SVG sanitization.
 * Uses the enshrined/svg-sanitize library for maximum security.
 */
class AllowSvgUpload {
	private array $settings;
	private const SVG_MIME_TYPE            = 'image/svg+xml';
	private const SVG_MIN_SANITIZED_SIZE   = 50;
	private const SVG_DEFAULT_DIMENSIONS   = [
		'width'  => 300,
		'height' => 300,
	];
	private const SVG_THUMBNAIL_DIMENSIONS = [
		'width'  => 150,
		'height' => 150,
	];
	private const SVG_MEDIUM_DIMENSIONS    = [
		'width'  => 200,
		'height' => 200,
	];
	
	private const SVG_EXTENSIONS = [ 'svg', 'svgz' ];
	
	public function __construct() {
		$this->settings = Plugin::get_settings( 'media' );
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		if( !empty( $this->settings['allow_svg_upload'] ) ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
			
			add_filter( 'adminease_localize_script', [ $this, 'adminease_localize_script' ] );
			add_filter( 'upload_mimes', [ $this, 'add_svg_to_upload_mimes' ] );
			add_filter( 'wp_check_filetype_and_ext', [ $this, 'fix_svg_mime_type' ], 10, 4 );
			add_filter( 'wp_prepare_attachment_for_js', [ $this, 'show_svg_in_media_library' ], 10, 3 );
			add_filter( 'wp_handle_upload_prefilter', [ $this, 'validate_svg_upload' ] );
			add_filter( 'wp_handle_upload', [ $this, 'sanitize_svg_upload' ], 10, 2 );
		}
	}
	
	/**
	 * Adds additional settings fields for the admin interface, particularly for managing media options.
	 *
	 * @param array $fields An associative array of existing settings fields. Each key represents a settings group, and the value is an array of fields associated with that group.
	 *
	 * @return array The modified settings fields array, including the new option to enable or disable SVG uploads with associated configuration details.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$fields['media']['fields'][] = [
			'type'        => 'switch',
			'id'          => 'allow-svg-upload',
			'name'        => 'adminease[media][allow_svg_upload]',
			'value'       => $this->settings['allow_svg_upload'] ?? false,
			'label_class' => 'adminease-switch',
			'input_class' => 'form-control',
			'label'       => __( 'Allow SVG Upload', 'adminease' ),
			'description' => __( 'Enable SVG file uploads to the WordPress media library with built-in security validation. SVG files are automatically scanned for malicious content including JavaScript, dangerous elements, and external references.', 'adminease' ),
		];
		
		return $fields;
	}
	
	/**
	 * Adds support for SVG file formats to the list of allowed upload MIME types.
	 *
	 * @param array $mimes An associative array of MIME types keyed by file extension.
	 *
	 * @return array The filtered list of MIME types with support for SVG added.
	 */
	public function add_svg_to_upload_mimes( array $mimes ): array {
		foreach( self::SVG_EXTENSIONS as $extension ) {
			$mimes[ $extension ] = self::SVG_MIME_TYPE;
		}
		
		return $mimes;
	}
	
	/**
	 * Fixes the MIME type for SVG files during file upload or handling.
	 *
	 * @param array      $data File data containing type and extension information.
	 * @param string     $file Path to the file.
	 * @param string     $filename Name of the file being processed, including the extension.
	 * @param array|null $mimes List of allowed MIME types (can be null).
	 *
	 * @return array Modified file data with corrected MIME type and extension for SVG files.
	 */
	public function fix_svg_mime_type( array $data, string $file, string $filename, ?array $mimes ): array {
		$ext = $this->extract_file_extension( $data, $filename );
		
		if( in_array( $ext, self::SVG_EXTENSIONS, true ) ) {
			$data['type'] = self::SVG_MIME_TYPE;
			$data['ext']  = $ext;
		}
		
		return $data;
	}
	
	/**
	 * Validate SVG file upload to ensure it adheres to defined criteria.
	 * ONLY processes SVG files, returns other files unchanged.
	 *
	 * @param array $file An associative array describing the uploaded file.
	 *                    Includes information such as file type, size, and path.
	 *
	 * @return array The validated file array, potentially modified or with an error message.
	 */
	public function validate_svg_upload( array $file ): array {
		// Early return if not an SVG file - this is crucial!
		if( !$this->is_svg_upload( $file ) ) {
			return $file;
		}
		
		if( !isset( $file['size'] ) || !is_numeric( $file['size'] ) ) {
			return $file;
		}
		
		// Get max upload size from settings, fallback to WordPress max upload size
		$max_upload_size = 0;
		
		if( !empty( $this->settings['upload_max_file_size'] ) && (int) $this->settings['upload_max_file_size'] > 0 ) {
			// Convert MB to bytes (setting is stored in MB)
			$max_upload_size = (int) $this->settings['upload_max_file_size'] * 1048576;
		} else {
			$wp_max          = wp_max_upload_size();
			$max_upload_size = $wp_max > 0 ? $wp_max : 10485760; // 10MB default fallback
		}
		
		if( $file['size'] > $max_upload_size ) {
			$file['error'] = sprintf(
			/* translators: %s is the maximum allowed size in MB */
				esc_html__( 'SVG file is too large. Maximum allowed size is %s MB.', 'adminease' ),
				number_format( $max_upload_size / 1048576, 1 )
			);
			
			return $file;
		}
		
		return $this->validate_svg_structure( $file );
	}
	
	/**
	 * Sanitizes uploaded SVG files to ensure they are safe for use.
	 * ONLY processes SVG files, returns other files unchanged.
	 *
	 * @param array $file An array containing information about the file being uploaded.
	 * @param mixed $overrides An array of upload overrides passed to the function.
	 *
	 * @return array The sanitized file data or error information if sanitization fails.
	 */
	public function sanitize_svg_upload( array $file, $overrides ): array {
		// Early return if not an SVG file - this is crucial!
		if( !$this->is_svg_file( $file ) ) {
			return $file;
		}
		
		try {
			$svg_content = $this->read_svg_content( $file );
			
			if( false === $svg_content ) {
				return $this->create_file_error( $file, esc_html__( 'Could not read uploaded SVG file for sanitization.', 'adminease' ) );
			}
			
			$sanitizer = $this->create_sanitizer();
			
			if( !$sanitizer ) {
				return $this->create_file_error( $file, esc_html__( 'SVG sanitization library not found. Please check composer dependencies.', 'adminease' ) );
			}
			
			return $this->process_svg_sanitization( $file, $svg_content, $sanitizer );
		}
		catch( \Throwable $e ) {
			return $this->handle_sanitization_error( $file, $e );
		}
	}
	
	/**
	 * Modifies the response data for SVG files in the WordPress media library.
	 *
	 * @param array       $response The response array for a single attachment.
	 * @param object      $attachment The attachment object.
	 * @param array|false $meta The attachment meta data array or false if no metadata.
	 *
	 * @return array The modified response array with SVG-specific data.
	 */
	public function show_svg_in_media_library( array $response, object $attachment, $meta ): array {
		if( self::SVG_MIME_TYPE !== $response['mime'] ) {
			return $response;
		}
		
		$response['image'] = $this->create_image_size( $response['url'], self::SVG_DEFAULT_DIMENSIONS );
		$response['thumb'] = $this->create_image_size( $response['url'], self::SVG_THUMBNAIL_DIMENSIONS );
		
		$response['sizes'] = [
			'full'      => $this->create_image_size( $response['url'], self::SVG_DEFAULT_DIMENSIONS, 'landscape' ),
			'medium'    => $this->create_image_size( $response['url'], self::SVG_MEDIUM_DIMENSIONS, 'landscape' ),
			'thumbnail' => $this->create_image_size( $response['url'], self::SVG_THUMBNAIL_DIMENSIONS, 'landscape' ),
		];
		
		// Ensure meta is an array before adding to it
		if( !is_array( $response['meta'] ) ) {
			$response['meta'] = [];
		}
		
		$response['meta']['sanitized'] = true;
		
		return $response;
	}
	
	/**
	 * Enqueues admin-specific scripts for the WordPress admin area.
	 *
	 * @param string $hook The current admin page hook suffix, used to determine
	 *                     if the scripts should be enqueued for the specific page.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts( string $hook ) {
		if( !$this->should_enqueue_scripts( $hook ) ) {
			return;
		}
		
		wp_add_inline_script( 'jquery', $this->get_svg_admin_script() );
	}
	
	/**
	 * Localizes script data with translations and messages for SVG upload success and errors.
	 *
	 * @param array $data An associative array containing script data to be localized. This array will be modified to include i18n strings for messages related to SVG uploads and sanitization.
	 *
	 * @return array The modified data array, including localized i18n strings for SVG upload success, sanitization, and mime type error messages.
	 */
	public function adminease_localize_script( array $data ): array {
		$data['i18n']['svgUploadSuccess']            = esc_html__( 'SVG uploaded and sanitized successfully!', 'adminease' );
		$data['i18n']['svgUploadSuccessDescription'] = esc_html__( 'Your SVG file has been professionally sanitized for security.', 'adminease' );
		$data['i18n']['svgSanitizedLabel']           = esc_html__( '✓ Sanitized', 'adminease' );
		$data['i18n']['mimeTypeNotRecognized']       = esc_html__( 'The uploaded mime type is not recognized.', 'adminease' );
		
		return $data;
	}
	
	/**
	 * Check if the uploaded file is an SVG based on file type during upload validation.
	 * This is used in the wp_handle_upload_prefilter hook.
	 *
	 * @param array $file File array from wp_handle_upload_prefilter
	 *
	 * @return bool True if file is SVG, false otherwise
	 */
	private function is_svg_upload( array $file ): bool {
		// Check MIME type first
		if( isset( $file['type'] ) && $file['type'] === self::SVG_MIME_TYPE ) {
			return true;
		}
		
		// Check file extension as fallback
		if( isset( $file['name'] ) ) {
			$ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
			
			return in_array( $ext, self::SVG_EXTENSIONS, true );
		}
		
		return false;
	}
	
	/**
	 * Extracts the file extension based on provided data or filename.
	 *
	 * @param array  $data Associative array that may contain the file extension under the 'ext' key.
	 * @param string $filename The name of the file used to extract the extension if not found in the data.
	 *
	 * @return string The file extension, determined either from the data or the filename.
	 */
	private function extract_file_extension( array $data, string $filename ): string {
		$ext = isset( $data['ext'] ) ? $data['ext'] : '';
		
		if( strlen( $ext ) < 1 ) {
			$exploded = explode( '.', $filename );
			$ext      = strtolower( end( $exploded ) );
		}
		
		return $ext;
	}
	
	/**
	 * Validates the size of the uploaded SVG file against the maximum allowed size.
	 *
	 * @param array $file An array containing file information, including its size.
	 *                    The method checks the 'size' key and sets an error message
	 *                    in the 'error' key if the file exceeds the defined size limit.
	 *
	 * @return array The updated file array, potentially including an error message
	 *               if the file size exceeds the maximum allowed size.
	 */
	private function validate_file_size( array $file ): array {
		if( !isset( $file['size'] ) || !is_numeric( $file['size'] ) ) {
			return $file;
		}
		
		// Get max upload size from settings, fallback to WordPress max upload size
		$max_upload_size = !empty( $this->settings['upload_max_file_size'] )
			? (int) $this->settings['upload_max_file_size']
			: wp_max_upload_size();
		
		if( $file['size'] > $max_upload_size ) {
			$file['error'] = sprintf(
			/* translators: %s is the maximum allowed size in MB */
				esc_html__( 'SVG file is too large. Maximum allowed size is %s MB.', 'adminease' ),
				number_format( $max_upload_size / 1048576, 1 )
			);
		}
		
		return $file;
	}
	
	/**
	 * Validates the structure of the provided SVG file to ensure it is safe and correct.
	 *
	 * @param array $file An associative array representing the uploaded file. It should include a 'tmp_name' key for the temporary file path and an 'error' key to store any error messages encountered during validation.
	 *
	 * @return array The updated file array, potentially with an error message added to the 'error' key if the SVG file is invalid or unreadable.
	 */
	private function validate_svg_structure( array $file ): array {
		$svg_content = file_get_contents( $file['tmp_name'] );
		
		if( $svg_content === false ) {
			$file['error'] = esc_html__( 'Could not read SVG file.', 'adminease' );
			
			return $file;
		}
		
		if( !$this->is_valid_svg_structure( $svg_content ) ) {
			$file['error'] = esc_html__( 'Invalid SVG file structure.', 'adminease' );
			
			return $file;
		}
		
		return $file;
	}
	
	/**
	 * Checks if the provided file is an SVG file by validating its MIME type and existence.
	 *
	 * @param array $file An associative array representing the file to check. It should include:
	 *                    - 'type': The MIME type of the file, which is compared against the SVG MIME type constant.
	 *                    - 'file': The path to the file, which is checked for existence.
	 *
	 * @return bool True if the file exists and its MIME type matches the expected SVG MIME type, otherwise false.
	 */
	private function is_svg_file( array $file ): bool {
		return isset( $file['type'] ) &&
		       $file['type'] === self::SVG_MIME_TYPE &&
		       isset( $file['file'] ) &&
		       file_exists( $file['file'] );
	}
	
	/**
	 * Reads the content of the specified SVG file and returns it as a string.
	 *
	 * @param array $file An associative array representing the SVG file. It should include a 'file' key containing the file path to the SVG file.
	 *
	 * @return false|string The content of the SVG file as a string, or false if the file could not be read.
	 */
	private function read_svg_content( array $file ) {
		return file_get_contents( $file['file'] );
	}
	
	/**
	 * Creates and configures an instance of the SVG sanitizer if the required class is available.
	 * @return Sanitizer|false Returns an instance of the configured Sanitizer class if it exists, or false if the required class is not available.
	 */
	private function create_sanitizer() {
		if( !class_exists( 'enshrined\svgSanitize\Sanitizer' ) ) {
			return false;
		}
		
		$sanitizer = new Sanitizer();
		$sanitizer->removeRemoteReferences( true );
		$sanitizer->removeXMLTag( true );
		$sanitizer->minify( false );
		
		return $sanitizer;
	}
	
	/**
	 * Processes the sanitization of an SVG file to ensure it is safe and secure for use.
	 *
	 * @param array  $file An associative array representing the uploaded file. It should include keys such as 'file' for the file path and may be updated with error information if sanitization fails.
	 * @param string $svg_content The raw content of the SVG file to be sanitized.
	 * @param object $sanitizer An instance of a sanitizer object that provides a `sanitize` method to clean the SVG content.
	 *
	 * @return array The updated file array, which may include additional error information in the case of sanitization failure, or remain unchanged if successful.
	 */
	private function process_svg_sanitization( array $file, string $svg_content, object $sanitizer ): array {
		$sanitized_content = $sanitizer->sanitize( $svg_content );
		
		if( $sanitized_content === false || empty( $sanitized_content ) ) {
			return $this->create_file_error( $file, esc_html__( 'SVG file could not be sanitized. It may contain dangerous content.', 'adminease' ) );
		}
		
		if( strlen( $sanitized_content ) < self::SVG_MIN_SANITIZED_SIZE ) {
			return $this->create_file_error( $file, esc_html__( 'SVG file became too small after sanitization. Upload rejected for security.', 'adminease' ) );
		}
		
		if( file_put_contents( $file['file'], $sanitized_content ) === false ) {
			return $this->create_file_error( $file, esc_html__( 'Could not save sanitized SVG file.', 'adminease' ) );
		}
		
		return $file;
	}
	
	/**
	 * Adds an error message to the provided file array.
	 *
	 * @param array  $file An associative array representing the file. It should include an 'error' key to store the provided error message.
	 * @param string $message The error message to be added to the file array.
	 *
	 * @return array The updated file array with the error message assigned to the 'error' key.
	 */
	private function create_file_error( array $file, string $message ): array {
		$file['error'] = $message;
		
		return $file;
	}
	
	/**
	 * Handles errors that occur during the sanitization process for an SVG file.
	 *
	 * @param array      $file An associative array representing the uploaded file. This array will be used to log the sanitization error and updated with an error message if needed.
	 * @param \Throwable $e The exception or error thrown during the sanitization process.
	 *
	 * @return array The updated file array with an error message describing the sanitization failure.
	 */
	private function handle_sanitization_error( array $file, \Throwable $e ): array {
		return $this->create_file_error(
			$file,
			sprintf(
			/* translators: %s is the error message from the exception */
				esc_html__( 'SVG sanitization failed: %s', 'adminease' ),
				$e->getMessage()
			)
		);
	}
	
	/**
	 * Creates an image size array containing the source URL, dimensions, and optional orientation for an image.
	 *
	 * @param string      $url The URL or source path of the image.
	 * @param array       $dimensions An associative array containing 'width' and 'height' keys representing the dimensions of the image.
	 * @param string|null $orientation Optional. The orientation of the image (e.g., 'landscape', 'portrait'). Default is null.
	 *
	 * @return array An associative array representing the image size, including 'src', 'url', 'width', 'height', and optionally 'orientation' if provided.
	 */
	private function create_image_size( string $url, array $dimensions, string $orientation = null ): array {
		$size = [
			'src'    => $url,
			'url'    => $url,
			'width'  => $dimensions['width'],
			'height' => $dimensions['height'],
		];
		
		if( $orientation ) {
			$size['orientation'] = $orientation;
		}
		
		return $size;
	}
	
	/**
	 * Determines whether scripts should be enqueued based on the given hook name.
	 *
	 * @param string $hook The current hook name to check. Typically represents the specific screen or context in which the logic is executed.
	 *
	 * @return bool Returns true if the hook includes 'upload', 'media', or 'post'; otherwise, false.
	 */
	private function should_enqueue_scripts( string $hook ): bool {
		return false !== strpos( $hook, 'upload' ) ||
		       false !== strpos( $hook, 'media' ) ||
		       false !== strpos( $hook, 'post' );
	}
	
	/**
	 * Generates and returns the JavaScript code responsible for enhancing the WordPress media uploader's functionality
	 * by customizing the preview display for SVG files.
	 * The script applies styling adjustments to the preview thumbnail of SVG files in the media uploader, ensuring
	 * proper display dimensions and visual enhancements. Additionally, it adds a "Sanitized" notice to indicate
	 * the SVG file has been sanitized.
	 * @return string The JavaScript code implementing the SVG preview customization in the media uploader.
	 */
	private function get_svg_admin_script(): string {
		return '
			jQuery(document).ready(function($) {
				$(document).on("click", ".attachment[data-subtype=\'svg+xml\']", function() {
					setTimeout(function() {
						var preview = $(".attachment-details .thumbnail img");
						if (preview.length && preview.attr("src").indexOf(".svg") !== -1) {
							preview.css({
								"max-width": "100%",
								"max-height": "300px",
								"width": "auto",
								"height": "auto",
								"object-fit": "contain",
								"background": "#f9f9f9",
								"border": "1px solid #ddd",
								"border-radius": "4px",
								"padding": "10px"
							});
							
							if (!$(".svg-sanitized-notice").length) {
								$(".attachment-details .thumbnail").append(
									"<div class=\"svg-sanitized-notice\" style=\"position: absolute; top: 5px; right: 5px; background: #00a32a; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px; font-weight: bold;\">✓ Sanitized</div>"
								);
							}
						}
					}, 100);
				});
			});
		';
	}
	
	/**
	 * Checks if the provided SVG content string has a valid structure.
	 *
	 * @param string $content The SVG content as a string. It should include the SVG markup to be validated.
	 *
	 * @return bool Returns true if the SVG structure is valid and well-formed; otherwise, false.
	 */
	private function is_valid_svg_structure( string $content ): bool {
		if( false === strpos( $content, '<svg' ) ) {
			return false;
		}
		
		$svg_open_count  = substr_count( strtolower( $content ), '<svg' );
		$svg_close_count = substr_count( strtolower( $content ), '</svg>' );
		
		if( $svg_open_count !== $svg_close_count ) {
			return false;
		}
		
		$old_setting = libxml_use_internal_errors( true );
		$dom         = new \DOMDocument();
		$is_valid    = $dom->loadXML( $content );
		libxml_use_internal_errors( $old_setting );
		
		return $is_valid;
	}
}