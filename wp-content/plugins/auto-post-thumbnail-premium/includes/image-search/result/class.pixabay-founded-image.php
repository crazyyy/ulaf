<?php

namespace WBCR\APT;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class PixabayFoundedImage extends FoundedImage {

	/**
	 * Parse image data
	 *
	 * @param array $item
	 * @param array $more_info
	 */
	protected function parse( $item, $more_info = [] ) {
		$preview_url = $item['previewURL'];
		$pos         = strpos( $preview_url, 'cdn.' );
		if ( $pos === false ) {
			$pos = - 1;
		}

		$preview_url = str_replace( '_150', $pos >= 0 ? '__340' : '_340', $preview_url );

		$this->link           = $item['largeImageURL'] ?? '';
		$this->title          = $item['tags'] ?? '';
		$this->context_link   = $item['pageURL'] ?? '';
		$this->thumbnail_link = $preview_url ?? '';

		$this->image         = new \stdClass();
		$this->image->mime   = $item['mime'] ?? 'image/jpeg';
		$this->image->size   = $item['imageSize'] ?? '';
		$this->image->width  = $item['webformatWidth'] ? $item['webformatWidth'] * 2 : '';
		$this->image->height = $item['webformatHeight'] ? $item['webformatHeight'] * 2 : '';

		preg_match_all( '/.*\/(.*)\.(\w{3,4})?(\?|\/.*)?$/', $this->link, $match );

		$this->file       = new \stdClass();
		$this->file->name = $match[1][0] ?? '';
		$this->file->ext  = $match[2][0] ?? '';

		$this->more_info = $more_info;
	}
}
