<?php

namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Yipresser\AdminOptimizer\Vendor\enshrined\svgSanitize\Sanitizer;

// TODO
// add svg optimization option https://github.com/svg/svgo.
// add user roles selection.

/**
 * SVG_Upload class
 */
class SVG_Upload {


	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'upload_mimes', [ $this, 'enable_svg_upload' ] );
		add_filter( 'wp_handle_upload_prefilter', [ $this, 'sanitize_svg_upload' ] );
		add_filter( 'wp_handle_sideload_prefilter', [ $this, 'sanitize_svg_upload' ] );
		add_filter( 'wp_check_filetype_and_ext', [ $this, 'check_svg_upload' ], 10, 4 );
		add_action( 'rest_insert_attachment', [ $this, 'sanitize_after_svg_upload' ], 10, 3 );
		add_filter( 'wp_generate_attachment_metadata', [ $this, 'generate_svg_metadata' ], 10, 2 );
		add_filter( 'wp_calculate_image_srcset', [ $this, 'disable_svg_srcset' ], 10, 4 );
		add_filter( 'wp_prepare_attachment_for_js', [ $this, 'get_svg_url_in_media_library' ] );
		add_filter( 'wp_get_attachment_metadata', [ $this, 'metadata_error_fix' ], 10, 2 );
	}

	/**
	 * Enable svg upload
	 *
	 * @param array $mimes List of mime types.
	 *
	 * @return array
	 */
	public function enable_svg_upload( $mimes ) {
		if ( current_user_can( 'upload_files' ) ) {
			$mimes['svg']  = 'image/svg+xml';
			$mimes['svgz'] = 'image/svg+xml';
		}
		return $mimes;
	}

	/**
	 * Check svg upload
	 *
	 * @param array  $data Uploaded file datatype.
	 * @param array  $file Uploaded file.
	 * @param string $filename Uploaded filename.
	 * @param array  $mimes Uploaded file mime type.
	 *
	 * @return array|string[]
	 */
	public function check_svg_upload( $data, $file, $filename, $mimes = [] ) {
		// Ensure $mimes is an array.
		$mimes = is_array( $mimes ) ? $mimes : [];

		// Check file type against mime types.
		$filetype = wp_check_filetype( $filename, $mimes );

		// Only process SVG files.
		if ( 'svg' === $filetype['ext'] || 'svgz' === $filetype['ext'] ) {
			// Check user capabilities.
			if ( ! current_user_can( 'upload_files' ) ) {
				return [ 'error' => __( 'You do not have permission to upload SVG files.', 'admin-optimizer' ) ];
			}
			$data = [
				'ext'  => 'svg',
				'type' => 'image/svg+xml',
			];
			if ( 'svgz' === $filetype['ext'] ) {
				$data['ext'] = 'svgz';
			}
		}

		return $data;
	}

	/**
	 * Check if the file is an SVG, if so handle appropriately
	 *
	 * @param array $file An array of data for a single file.
	 *
	 * @return array
	 */
	public function sanitize_svg_upload( array $file ) {
		if ( ! isset( $file['tmp_name'] ) ) {
			return $file;
		}

		$file_name     = $file['name'] ?? '';
		$file_type_ext = wp_check_filetype_and_ext( $file['tmp_name'], $file_name );
		$file_type     = ! empty( $file_type_ext['type'] ) ? $file_type_ext['type'] : '';

		if ( 'image/svg+xml' === $file_type ) {
			$uploaded_svg = file_get_contents( $file['tmp_name'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

			// Check if the file is svgz (gzipped).
			$is_gzipped = $this->is_svg_gzipped( $uploaded_svg );
			if ( $is_gzipped ) {
				$uploaded_svg = gzdecode( $uploaded_svg );
			}

			if ( false === $uploaded_svg ) {
				$file['error'] = __( 'This SVG file could not be sanitized, and could not be uploaded.', 'admin-optimizer' );
			} else {
				$sanitizer = new Sanitizer();
				$sanitizer->minify( true );
				$sanitized_svg = $sanitizer->sanitize( $uploaded_svg );
				if ( false === $sanitized_svg ) {
					$file['error'] = __( 'This SVG file could not be sanitized, and could not be uploaded.', 'admin-optimizer' );
				} else {
					if ( $is_gzipped ) {
						$sanitized_svg = gzencode( $sanitized_svg );
					}
					file_put_contents( $file['tmp_name'], $sanitized_svg );  //phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
				}
			}
		}

		return $file;
	}


	/**
	 * Check if the contents are gzipped
	 *
	 * @see http://www.gzip.org/zlib/rfc-gzip.html#member-format
	 *
	 * @param string $contents Content to check.
	 *
	 * @return bool
	 */
	private function is_svg_gzipped( string $contents ) {
		// phpcs:disable Generic.Strings.UnnecessaryStringConcat.Found
		if ( function_exists( 'mb_strpos' ) ) {
			return 0 === mb_strpos( $contents, "\x1f" . "\x8b" . "\x08" );
		} else {
			return 0 === strpos( $contents, "\x1f" . "\x8b" . "\x08" );
		}
		// phpcs:enable
	}

	/**
	 * Sanitize SVG
	 *
	 * @param \WP_Post         $attachment Post attachment.
	 * @param \WP_REST_Request $request Rest Request.
	 * @param bool             $creating Is creating?.
	 *
	 * @return void
	 */
	public function sanitize_after_svg_upload( $attachment, $request, $creating ) {
		// Sanitize SVG before insert into the media library.
		if ( $creating ) {
			if ( $attachment instanceof \WP_Post ) {
				$file_path    = get_attached_file( $attachment->ID );
				$original_svg = file_get_contents( $file_path ); //phpcs:ignore

				$sanitizer = new Sanitizer();
				$sanitizer->minify( true );
				$sanitized_svg = $sanitizer->sanitize( $original_svg );

				if ( false !== $sanitized_svg ) {
					file_put_contents( $file_path, $sanitized_svg ); //phpcs:ignore
				}
			}
		}
	}

	/**
	 * Generate SVG metadata
	 *
	 * @param array $metadata SVG metadata.
	 * @param int   $attachment_id Attachment ID.
	 *
	 * @return mixed
	 */
	public function generate_svg_metadata( $metadata, $attachment_id ) {
		if ( 'image/svg+xml' === get_post_mime_type( $attachment_id ) ) {
			$svg_path = get_attached_file( $attachment_id );
			$svg      = simplexml_load_file( $svg_path );
			$width    = 0;
			$height   = 0;

			if ( $svg ) {

				$attributes = $svg->attributes();
				if ( isset( $attributes->width, $attributes->height ) ) {
					$width  = intval( floatval( $attributes->width ) );
					$height = intval( floatval( $attributes->height ) );
				} elseif ( isset( $attributes->viewBox ) ) { //phpcs:ignore
					$sizes = explode( ' ', $attributes->viewBox );  //phpcs:ignore
					if ( isset( $sizes[2], $sizes[3] ) ) {
						$width  = intval( floatval( $sizes[2] ) );
						$height = intval( floatval( $sizes[3] ) );
					}
				}
			}

			$metadata['width']  = $width;
			$metadata['height'] = $height;

			// Get SVG filename.
			$svg_url          = wp_get_original_image_url( $attachment_id );
			$svg_url_path     = str_replace( wp_upload_dir()['baseurl'] . '/', '', $svg_url );
			$metadata['file'] = $svg_url_path;

		}

		return $metadata;
	}

	/**
	 * Disable the creation of srcset on SVG images.
	 *
	 * @param array  $image_meta The image meta data.
	 * @param int[]  $size_array {
	 *     An array of requested width and height values.
	 *
	 *     @type int $0 The width in pixels.
	 *     @type int $1 The height in pixels.
	 * }
	 * @param string $image_src     The 'src' of the image.
	 * @param int    $attachment_id The image attachment ID.
	 */
	public function disable_svg_srcset( $image_meta, $size_array, $image_src, $attachment_id ) {
		if ( $attachment_id && 'image/svg+xml' === get_post_mime_type( $attachment_id ) && is_array( $image_meta ) ) {
			$image_meta['sizes'] = [];
		}

		return $image_meta;
	}

	/**
	 * Return svg file URL to show preview in media library
	 *
	 * @link https://developer.wordpress.org/reference/functions/wp_prepare_attachment_for_js/
	 * @since 2.6.0
	 *
	 * @param array $response Response.
	 */
	public function get_svg_url_in_media_library( $response ) {

		// Check response mime type.
		if ( 'image/svg+xml' === $response['mime'] ) {

			$response['image'] = [
				'src' => $response['url'],
			];

		}

		return $response;
	}

	/**
	 * Filters the attachment meta data.
	 *
	 * @param array|bool $data Array of meta data for the given attachment, or false
	 *                            if the object does not exist.
	 * @param int        $post_id Attachment ID.
	 */
	public function metadata_error_fix( $data, $post_id ) {

		// If it's a WP_Error regenerate metadata and save it.
		if ( is_wp_error( $data ) ) {
			$data = wp_generate_attachment_metadata( $post_id, get_attached_file( $post_id ) );
			wp_update_attachment_metadata( $post_id, $data );
		}

		return $data;
	}
}
