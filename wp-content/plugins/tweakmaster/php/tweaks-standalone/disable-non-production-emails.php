<?php
/**
 * @package   Nextgenthemes\TweakMaster
 * @link      https://nexgenthemes.com
 * @copyright 2025 Nicolas Jonas
 * @license   GPL-3.0
 *
 * @wordpress-plugin
 * Plugin Name:      Disable non production emails
 * Description:      If WP_ENV (Trellis) or wp_get_environment_type is not production, emails sending is mocked
 * Plugin URI:       https://nexgenthemes.com/plugins/tweakmaster/
 * Version:          1.0.0
 * Author:           Nicolas Jonas
 * Author URI:       https://nexgenthemes.com
 * License:          GPLv3
 */

declare(strict_types = 1);

namespace Nextgenthemes\TweakMaster;

// Disable all emails by filtering wp_mail
add_filter( 'wp_mail', __NAMESPACE__ . '\disable_non_production_emails', 9999 );

/**
 * Disable all emails by filtering wp_mail
 *
 * @param array<string, mixed> $args Array of arguments for wp_mail.
 * @return array<string, mixed>      Modified array of arguments.
 */
function disable_non_production_emails( array $args ): array {

	$trellis_not_production = getenv( 'WP_ENV' ) && 'production' !== getenv( 'WP_ENV' );
	$wp_not_production      = function_exists( 'wp_get_environment_type' ) && 'production' !== wp_get_environment_type();

	if ( $trellis_not_production || $wp_not_production ) {
		// Return success without sending the email
		return [
			'to'          => $args['to'],
			'subject'     => $args['subject'],
			'message'     => $args['message'],
			'headers'     => $args['headers'],
			'attachments' => $args['attachments'],
			'sent'        => true,
		];
	}

	return $args;
}
