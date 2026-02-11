<?php

namespace DebugLogViewer\Admin\Helpers;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Utils {

	public static function verifyNonce( $nonce, $action = 'ajax_nonce' ) {
		if (!wp_verify_nonce($nonce, $action)) {
			wp_send_json_error(__('Please refresh the page', 'debug-log-viewer'));
		}
	}

	public static function verifyAdminCapability() {
		if (!current_user_can('manage_options')) {
			wp_send_json_error(__('Insufficient permissions', 'debug-log-viewer'));
		}
	}

	public static function getDocumentRoot() {
		return isset($_SERVER['DOCUMENT_ROOT'])
			? sanitize_text_field(wp_unslash($_SERVER['DOCUMENT_ROOT']))
			: '';
	}
}
