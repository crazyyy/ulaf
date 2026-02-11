<?php

namespace WBCR\APT;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class UnsplashFoundedImage extends FoundedImage {

	/**
	 * Parse image data
	 *
	 * @param array $item
	 * @param array $more_info
	 */
	protected function parse( $item, $more_info = [] ) {
		$this->link           = $item['urls']['raw'] ?? '';
		$this->title          = $item['description'] ?? '';
		$this->context_link   = $item['links']['html'] ?? '';
		$this->thumbnail_link = $item['urls']['small'] ?? '';

		$this->image         = new \stdClass();
		$this->image->mime   = $item['mime'] ?? 'image/jpeg';
		$this->image->size   = $item['imageSize'] ?? '';
		$this->image->width  = $item['width'] ?? '';
		$this->image->height = $item['height'] ?? '';

		preg_match_all( '/.*\/(.*)\.(\w{3,4})?(\?|\/.*)?$/', $this->link, $match );

		$this->file       = new \stdClass();
		$this->file->name = $match[1][0] ?? '';
		$this->file->ext  = $match[2][0] ?? '';

		$this->more_info = $more_info;
	}
}
