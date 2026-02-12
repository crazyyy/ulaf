<?php
namespace AdminEase\Features;

use AdminEase\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Class UploadMaxFileSize
 * Handles operations related to upload_max_filesize and post_max_size PHP ini directives for system configurations.
 */
class UploadMaxFileSize {
	private array $settings;
	
	public function __construct() {
		$this->settings = Plugin::get_settings( 'media' );
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		add_action( 'adminease_settings_saved', [ $this, 'adminease_settings_saved' ] );
		
		add_filter( 'wp_handle_upload_prefilter', [ $this, 'validate_upload_file_size' ] );
	}
	
	/**
	 * Modify and add custom settings fields to the AdminEase configuration.
	 *
	 * @param array $fields An associative array of existing fields, grouped by categories.
	 *
	 * @return array The modified array of settings fields, including custom fields.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$fields['media']['fields'][] = [
			'type'              => 'number',
			'id'                => 'upload-max-file-size',
			'name'              => 'adminease[media][upload_max_file_size]',
			'value'             => $this->settings['upload_max_file_size'] ?? '',
			'label_class'       => 'adminease-label',
			'input_class'       => 'form-control',
			'label'             => __( 'Upload Max File Size', 'adminease' ),
			'description'       => __( 'The <strong>Upload Max File Size</strong> setting in WordPress controls the maximum size of files that can be uploaded to your site. This limit is set by your server configuration, but you can adjust it in WordPress to allow larger files like high-resolution images, videos, or documents. If you try to upload a file larger than this limit, WordPress will display an error message and prevent the upload.', 'adminease' ),
			'field_description' => __( 'Set the maximum file size allowed for uploads in megabytes (MB). Enter a numeric value only (e.g., "64" for 64MB, "128" for 128MB). Leave empty to use server default. This affects the upload_max_filesize PHP setting.', 'adminease' ),
			'attributes'        => [
				'placeholder' => __( 'e.g., 64', 'adminease' ),
				'min'         => '1',
				'max'         => '2048',
				'step'        => '1',
				'pattern'     => '[0-9]+',
				'inputmode'   => 'numeric',
			],
		];
		
		return $fields;
	}
	
	/**
	 * Save the AdminEase settings and update corresponding PHP ini directives.
	 *
	 * @param array $sanitized_settings The sanitized settings array to be saved.
	 *
	 * @return void
	 */
	public function adminease_settings_saved( array $sanitized_settings ): void {
		$file_handler = Plugin::$FileHandler;
		
		// Check if upload_max_file_size is set and not empty
		if( !empty( $sanitized_settings['media']['upload_max_file_size'] ) ) {
			$upload_max_size = $sanitized_settings['media']['upload_max_file_size'];
			
			if( $upload_max_size > 0 ) {
				// Set the ini directives
				$size_value = $upload_max_size . 'M';
				
				$file_handler->stack_wp_config_ini_directive( 'upload_max_filesize', $size_value );
				
				// Also update post_max_size to be slightly larger to accommodate file uploads
				$post_max_size   = $this->calculate_post_max_size( $upload_max_size );
				$post_size_value = $post_max_size . 'M';
				
				$file_handler->stack_wp_config_ini_directive( 'post_max_size', $post_size_value );
			} else {
				// Invalid size, remove the directives
				$this->remove_ini_directives( $file_handler );
			}
		} else {
			// Empty or not set, remove the directives
			$this->remove_ini_directives( $file_handler );
		}
	}
	
	/**
	 * Remove the upload file size related ini directives
	 *
	 * @param object $file_handler The file handler instance
	 *
	 * @return void
	 */
	private function remove_ini_directives( object $file_handler ): void {
		// Remove the ini directives by passing null or empty string
		$file_handler->stack_wp_config_ini_directive( 'upload_max_filesize', null );
		$file_handler->stack_wp_config_ini_directive( 'post_max_size', null );
	}
	
	/**
	 * Calculate appropriate post_max_size based on upload_max_filesize
	 * post_max_size should be larger than upload_max_filesize to account for form data
	 *
	 * @param int $upload_max_size Upload max size in MB
	 *
	 * @return int Post max size in MB
	 */
	private function calculate_post_max_size( int $upload_max_size ): int {
		// Add 25% buffer or minimum 8MB, whichever is larger
		$buffer = max( 8, ceil( $upload_max_size * 0.25 ) );
		
		return $upload_max_size + $buffer;
	}
	
	/**
	 * Get the current upload max file size from PHP settings
	 * @return string Current upload_max_filesize setting
	 */
	public static function get_current_upload_max_filesize(): string {
		return ini_get( 'upload_max_filesize' );
	}
	
	/**
	 * Get the current post max size from PHP settings
	 * @return string Current post_max_size setting
	 */
	public static function get_current_post_max_size(): string {
		return ini_get( 'post_max_size' );
	}
	
	/**
	 * Convert size string to bytes (helper function)
	 *
	 * @param string $size Size string (e.g., '64M', '2G')
	 *
	 * @return float Size in bytes
	 */
	public static function convert_size_to_bytes( string $size ): float {
		$size  = trim( $size );
		$unit  = strtoupper( substr( $size, -1 ) );
		$value = (float) $size;
		
		switch( $unit ) {
			case 'G':
				$value *= 1024 * 1024 * 1024;
				break;
			case 'M':
				$value *= 1024 * 1024;
				break;
			case 'K':
				$value *= 1024;
				break;
		}
		
		return $value;
	}
	
	/**
	 * Convert bytes to human-readable format
	 *
	 * @param int $bytes Size in bytes
	 *
	 * @return string Human-readable size
	 */
	public static function format_bytes( int $bytes ): string {
		$units = array( 'B', 'KB', 'MB', 'GB' );
		$size  = (float) $bytes; // Convert to float to handle division
		
		for( $i = 0; $size > 1024 && $i < count( $units ) - 1; $i++ ) {
			$size /= 1024;
		}
		
		return number_format_i18n( $size, 2 ) . ' ' . $units[ $i ];
	}
	
	/**
	 * Validate uploaded file size against configured limit
	 *
	 * @param array $file File upload array from WordPress
	 *
	 * @return array Modified file array with error if size exceeds limit
	 */
	public function validate_upload_file_size( array $file ): array {
		if( empty( $this->settings['upload_max_file_size'] ) ) {
			return $file;
		}
		
		// Convert MB to bytes for comparison
		$max_size_bytes = $this->settings['upload_max_file_size'] * 1024 * 1024;
		
		// Check if file size exceeds the limit
		if( !empty( $file['size'] ) && $file['size'] > $max_size_bytes ) {
			$file['error'] = sprintf(
			/* translators: 1: uploaded file size, 2: maximum allowed file size */
				__( 'File size (%1$s) exceeds the maximum allowed size of %2$s.', 'adminease' ),
				self::format_bytes( $file['size'] ),
				self::format_bytes( $max_size_bytes )
			);
		}
		
		return $file;
	}
}