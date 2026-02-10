<?php

namespace WBCR\APT;

use WP_Post, WP_Error;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Class PostImagesPro
 *
 * @package WBCR\APT
 */
class PostImagesPro extends PostImages {

	/**
	 * @return string
	 */
	public function upload_and_replace_images() {
		$content  = $this->post->post_content;
		$site_url = parse_url( home_url(), PHP_URL_HOST );

		foreach ( $this->get_images() as $image ) {
			if ( false !== strpos( $image['url'], $site_url ) ) {
				continue;
			}

			$thumb_id = $this->download_and_attachment( $image );
			if ( ! is_wp_error( $thumb_id ) ) {
				$replace = wp_get_attachment_image( $thumb_id, '', false, [ 'title' => $image['title'] ] );
				$content = str_replace( $image['tag'], $replace, $content );
			}
		}

		$this->post->post_content = $content;

		return $content;
	}

	/**
	 * @param array $image
	 *
	 * @return int|WP_Error
	 */
	public function download_and_attachment( $image ) {
		$thumb_id = 0;
		$download = null;
		$post     = $this->post;

		//$uploads   = wp_upload_dir( current_time( 'mysql' ) );
		$file_path = $this->unique_filepath( $image['url'] );
		$extension = pathinfo( $file_path, PATHINFO_EXTENSION );

		if ( empty( $extension ) ) {
			$download = $this->download( $image['url'], $file_path );
			if ( $download ) {
				$extension     = explode( '/', wp_get_image_mime( $file_path ) )[1] ?? 'jpg';
				$file_path_old = $file_path;
				$file_path     = $this->unique_filepath( "{$file_path}.{$extension}" );
				@rename( $file_path_old, $file_path );
			}
		}

		$download = $download ?? $this->download( $image['url'], $file_path );
		if ( $download ) {
			$thumb_id = AutoPostThumbnails::insert_attachment( $post, $file_path );
		}

		if ( is_wp_error( $thumb_id ) ) {
			@unlink( $file_path );
		}

		return $thumb_id;
	}
}
