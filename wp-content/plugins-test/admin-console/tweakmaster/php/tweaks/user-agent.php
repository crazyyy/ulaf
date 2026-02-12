<?php
/**
 * @package   Nextgenthemes\TweakMaster
 * @link      https://nexgenthemes.com
 * @copyright 2025 Nicolas Jonas
 * @license   GPL-3.0
 */

declare(strict_types = 1);

namespace Nextgenthemes\TweakMaster;

add_filter( 'http_headers_useragent', __NAMESPACE__ . '\set_user_agent' );
/**
 * Sets the user agent for HTTP requests.
 *
 * @link https://developer.wordpress.org/reference/functions/http_headers_useragent/
 *
 * @param string $agent The user agent string to process.
 * unused param string $url
 * @return string The modified user agent string.
 */
function set_user_agent( string $agent ): string {

	if ( 'default' === options()['user-agent'] ) {
		return $agent;
	}

	return options()['user-agent'];
}
