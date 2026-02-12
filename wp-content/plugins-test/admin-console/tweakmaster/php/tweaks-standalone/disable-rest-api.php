<?php
/**
 * @package   Nextgenthemes\TweakMaster
 * @link      https://nexgenthemes.com
 * @copyright 2025 Nicolas Jonas
 * @license   GPL-3.0
 *
 * @wordpress-plugin
 * Plugin Name:      Disable REST API
 * Description:      Disables the REST API
 * Plugin URI:       https://nexgenthemes.com/plugins/tweakmaster/
 * Version:          1.0.0
 * Author:           Nicolas Jonas
 * Author URI:       https://nexgenthemes.com
 * License:          GPLv3
 */

declare(strict_types = 1);

namespace Nextgenthemes\TweakMaster;

add_filter( 'rest_authentication_errors', __NAMESPACE__ . '\disable_rest_api' );

function disable_rest_api(): \WP_Error {
	return new \WP_Error(
		'rest_disabled',
		// Translators: The WordPress REST API has been disabled.
		__( 'The WordPress REST API has been disabled.', 'tweakmaster' ),
		array(
			'status' => rest_authorization_required_code(),
		)
	);
}
