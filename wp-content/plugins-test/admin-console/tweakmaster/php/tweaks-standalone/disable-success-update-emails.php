<?php
/**
 * @package   Nextgenthemes\TweakMaster
 * @link      https://nexgenthemes.com
 * @copyright 2025 Nicolas Jonas
 * @license   GPL-3.0
 *
 * @wordpress-plugin
 * Plugin Name:      Disable Success Update Emails
 * Description:      Disable Success Update Emails
 * Plugin URI:       https://nexgenthemes.com/plugins/tweakmaster/
 * Version:          1.0.0
 * Author:           Nicolas Jonas
 * Author URI:       https://nexgenthemes.com
 * License:          GPLv3
 */

declare(strict_types = 1);

namespace Nextgenthemes\TweakMaster;

add_filter( 'auto_core_update_send_email', __NAMESPACE__ . '\disable_core_auto_update_emails_on_success', 10, 2 );
/**
 * Disable auto-update email notifications.
 *
 * @param bool   $send        Whether to send an email.
 * @param string $type        The type of email to send.
 * @return bool               Whether to send an email.
 */
function disable_core_auto_update_emails_on_success( bool $send, string $type ): bool {

	if ( 'success' === $type ) {
		return false;
	}

	return $send;
}

add_filter( 'auto_plugin_update_send_email', __NAMESPACE__ . '\disable_auto_update_emails_on_success', 10, 2 );
add_filter( 'auto_theme_update_send_email', __NAMESPACE__ . '\disable_auto_update_emails_on_success', 10, 2 );
/**
 * Disable auto-update email notifications if all of the updates were successful.
 *
 * @param bool           $enabled        Whether to send an email.
 * @param array<object>  $update_results The result of the update.
 * @return bool                          Whether to send an email.
 */
function disable_auto_update_emails_on_success( bool $enabled, array $update_results ): bool {

	foreach ( $update_results as $update_result ) {
		if ( $update_result->result ) {
			return false;
		}
	}

	return $enabled;
}
