<?php

/**
 * Handles all customizations for the opt-in modal.
 *
 * @since 2.0.0
 */

namespace SolidWP\Mail\Telemetry;

class Modal {

	/**
	 * Customizes the optin_args
	 *
	 * @since 2.0.0
	 *
	 * @param array  $args          The arguments used to render the modal.
	 * @param string $stellar_slug  The StellarWP slug of the plugin displaying the modal.
	 *
	 * @return array<string, mixed>
	 */
	public function optin_args( array $args, string $stellar_slug ): array {
		if ( $stellar_slug !== 'solid-mail' ) {
			return $args;
		}

		$args['plugin_logo']        = WPSMTP_ASSETS_URL . 'images/solid-mail-logo.png';
		$args['plugin_logo_width']  = 233;
		$args['plugin_logo_height'] = 48;
		$args['permissions_url']    = 'https://go.solidwp.com/solid-mail-opt-in-usage-sharing';
		$args['tos_url']            = 'https://go.solidwp.com/solid-mail-terms-usage-modal';
		$args['privacy_url']        = 'https://go.solidwp.com/solid-mail-privacy-usage-modal';
		$args['plugin_logo_alt']    = 'Solid Mail Logo';
		$args['plugin_name']        = 'Solid Mail';

		$args['heading'] = sprintf(
			// Translators: The plugin name.
			esc_html__( 'We hope you love %s.', 'LION' ),
			$args['plugin_name']
		);

		$args['intro'] = $this->get_intro( $args['user_name'] );

		return $args;
	}

	/**
	 * Provides the intro text with the current users's display name inserted.
	 *
	 * @param string $user_name The user to which the modal is shown.
	 *
	 * @return string
	 */
	public function get_intro( string $user_name ): string {
		return sprintf(
			// Translators: The User's Name
			esc_html__( 'Hi %s. SolidWP is dedicated to delivering top-notch services, and your input helps us deliver on that promise. By opting into our feedback program, you help enhance the Solid Performance experience for yourself and all of our users. When you opt in, you allow us to access certain data related to how you use our products, which we use responsibly to tailor our products to your needs. You will additionally receive updates, important product and marketing information, and exclusive offers via email. You may unsubscribe at any time. We take data privacy seriously and adhere to the highest standards respecting all relevant regulations and guidelines. To join and help shape the future of Solid Mail and StellarWP, simply click “Allow & Continue” below.', 'LION' ),
			$user_name,
		);
	}
}
