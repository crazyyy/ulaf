<?php

namespace WBCR\APT;

use WAPT_Plugin, Exception;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WAPT_Unsplash implements ImageSearch {
	use \WAPT_ImagePerPage;

	const URL = 'https://api.unsplash.com/search/photos';

	private $key;

	private $orientation = null;

	public function __construct() {
		$this->key = WAPT_Plugin::app()->getOption( 'unsplash-apikey' );
	}

	/**
	 * @param mixed $orientation
	 *
	 * @return $this
	 */
	public function set_orientation( $orientation ) {
		$this->orientation = $orientation;

		return $this;
	}

	public function search( $query, $page ) {
		$params = [
			'per_page'  => $this->per_page,
			'page'      => $page,
			'query'     => $query,
			'client_id' => $this->key,
		];

		if ( $this->orientation && $this->orientation !== 'all' ) {
			$params['orientation'] = $this->orientation;
		}

		$url = sprintf( "%s?%s", self::URL, http_build_query( $params ) );

		$response = wp_remote_get( $url, [ 'timeout' => 100 ] );
		if ( is_wp_error( $response ) ) {
			throw new Exception( 'Error: ' . $response->get_error_message() );
		}

		$images   = [];
		$error    = null;
		$response = json_decode( $response['body'], true );
		if ( isset( $response['errors'] ) ) {
			$error = implode( " | ", $response['errors'] );
		} elseif ( isset( $response['results'] ) && is_array( $response['results'] ) ) {
			foreach ( $response['results'] as $img ) {
				$images[] = new UnsplashFoundedImage( $img );
			}
		}

		return new SearchResponse( $images, $error );
	}
}
