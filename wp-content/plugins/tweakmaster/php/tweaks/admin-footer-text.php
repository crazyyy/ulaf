<?php
/**
 * @package   Nextgenthemes\TweakMaster
 * @link      https://nexgenthemes.com
 * @copyright 2025 Nicolas Jonas
 * @license   GPL-3.0
 */

declare(strict_types = 1);

namespace Nextgenthemes\TweakMaster;

add_filter( 'admin_footer_text', __NAMESPACE__ . '\remove_footer_admin' );

function remove_footer_admin(): string {

	return wp_kses(
		options()['admin-footer-text'],
		array(
			'a' => array(
				'href'   => array(),
				'target' => array(),
			),
		)
	);
}
