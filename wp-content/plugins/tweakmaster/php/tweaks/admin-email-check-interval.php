<?php
/**
 * @package   Nextgenthemes\TweakMaster
 * @link      https://nexgenthemes.com
 * @copyright 2025 Nicolas Jonas
 * @license   GPL-3.0
 */

declare(strict_types = 1);

namespace Nextgenthemes\TweakMaster;

add_filter( 'admin_email_check_interval', __NAMESPACE__ . '\admin_email_check_interval' );

function admin_email_check_interval(): int {

	return options()['admin-email-check-interval'];
}
