<?php

/**
 * In WordPress 6.8 {@see WP_PHPMailer} class was introduced to improve error strings i18n.
 * The current conditional definition is required to extend the new class in WordPress 6.8 and preserve
 * backward compatibility for WordPress < 6.8.
 */

if ( file_exists( ABSPATH . WPINC . '/class-wp-phpmailer.php' ) ) {
	require_once ABSPATH . WPINC . '/class-wp-phpmailer.php';
} else {
	class WP_PHPMailer extends \PHPMailer\PHPMailer\PHPMailer {
	}
}
