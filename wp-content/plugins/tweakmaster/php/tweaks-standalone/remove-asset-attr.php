<?php
/**
 * @package   Nextgenthemes\TweakMaster
 * @link      https://nexgenthemes.com
 * @copyright 2025 Nicolas Jonas
 * @license   GPL-3.0
 *
 * @wordpress-plugin
 * Plugin Name:      Remove Asset Attributes
 * Description:      Remove id and media attributes from styles and scripts
 * Plugin URI:       https://nexgenthemes.com/plugins/tweakmaster/
 * Version:          1.0.0
 * Author:           Nicolas Jonas
 * Author URI:       https://nexgenthemes.com
 * License:          GPLv3
 */

declare(strict_types = 1);

namespace Nextgenthemes\TweakMaster;

use WP_HTML_Tag_Processor;

add_filter( 'style_loader_tag', __NAMESPACE__ . '\remove_link_attr', PHP_INT_MAX );
add_filter( 'script_loader_tag', __NAMESPACE__ . '\remove_script_attr', PHP_INT_MAX );

function remove_link_attr( string $html ): string {

	$p = new WP_HTML_Tag_Processor( $html );

	while ( $p->next_tag( 'link' ) ) {

		if ( 'all' === $p->get_attribute( 'media' ) ) {
			$p->remove_attribute( 'media' );
		}

		$p->remove_attribute( 'id' );
	}

	$html = $p->get_updated_html();

	return $html;
}

function remove_script_attr( string $html ): string {

	$p = new WP_HTML_Tag_Processor( $html );

	while ( $p->next_tag( 'script' ) ) {
		$p->remove_attribute( 'id' );
	}

	$html = $p->get_updated_html();

	return $html;
}
