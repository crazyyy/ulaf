<?php

namespace SolidWP\Mail\Contracts;

use WP_Error;

interface Api_Connector {
	/**
	 * Determines if email should be sent via API instead of SMTP.
	 *
	 * @param array{
	 *     to: array<array{0: string, 1: string}>,
	 *     cc: array<array{0: string, 1: string}>,
	 *     bcc: array<array{0: string, 1: string}>,
	 *     from: string,
	 *     sender: string,
	 *     subject: string,
	 *     headers: string,
	 *     body: string,
	 *     custom_headers: array<string, string>,
	 *     reply_to: array<array{0: string, 1: string}>,
	 *     all_recipients: array<string>,
	 *     message_type: string,
	 *     charset: string,
	 *     encoding: string
	 * } $email_data Email sending data including recipients, content, and headers.
	 *
	 * @return bool|WP_Error Returns true if email was sent successfully, WP_Error on failure.
	 */
	public function send_use_api( array $email_data );
}
