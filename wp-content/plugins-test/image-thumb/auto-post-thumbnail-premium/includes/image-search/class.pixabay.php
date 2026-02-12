<?php

namespace WBCR\APT;

use WAPT_Plugin, Exception;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WAPT_Pixabay implements ImageSearch {
	use \WAPT_ImagePerPage;

	const URL = 'https://pixabay.com/api/';

	private $key;

	private $image_type;

	private $orientation;

	public function __construct() {
		$this->key = WAPT_Plugin::app()->getOption( 'pixabay-apikey' );
	}

	/**
	 * @param string $image_type
	 *
	 * @return $this
	 */
	public function set_image_type( $image_type ) {
		$this->image_type = $image_type;

		return $this;
	}

	/**
	 * @param string $orientation
	 *
	 * @return $this
	 */
	public function set_orientation( $orientation ) {
		$this->orientation = $orientation;

		return $this;
	}

	public function search( $query, $page ) {
		$url = sprintf( "%s?%s", self::URL, http_build_query( [
			'per_page'    => $this->per_page,
			'page'        => $page,
			'image_type'  => $this->image_type,
			'orientation' => $this->orientation,
			'q'           => $query,
			'key'         => $this->key,
		] ) );

		$response = wp_remote_get( $url, [ 'timeout' => 100 ] );
		if ( is_wp_error( $response ) ) {
			throw new Exception( 'Error: ' . $response->get_error_message() );
		}

		$images        = [];
		$error         = null;
		$response_body = json_decode( $response['body'], true );
		if ( ! is_null( $response_body ) ) {
			if ( isset( $response_body['hits'] ) && is_array( $response_body['hits'] ) ) {
				foreach ( $response_body['hits'] as $img ) {
					$images[] = new PixabayFoundedImage( $img );
				}
			}
		} else {
			$error = $response['body'];
		}

		return new SearchResponse( $images, $error );
	}
}
